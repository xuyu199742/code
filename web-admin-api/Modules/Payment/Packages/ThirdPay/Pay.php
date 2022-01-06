<?php

namespace Modules\Payment\Packages\ThirdPay;

use App\Exceptions\NewException;
use App\Rules\ProviderExist;
use App\Rules\UserExist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\PaymentPassageway;
use Models\AdminPlatform\PaymentProvider;
use Modules\Payment\Packages\ThirdPay\Abstracts\PayAbstract;
use Modules\Payment\Packages\ThirdPay\Interfaces\PayTypes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;

class Pay
{
    //缓存sdk配置的缓存key
    private $cache_provider_key = 'payment_provider_configs';


    private $config;

    private $order;

    /**
     * Pay constructor.
     * 从payments.php中获取配置
     */
    public function __construct()
    {
        $this->config = config('payments');
        $this->cacheProviderConfig();
    }

    //缓存配置
    public function cacheProviderConfig()
    {
        if ($this->getProviderCofig()) return;
        $providers = $this->config['providers'];
        $data      = [];
        foreach ($providers as $key => $provider) {
            if (!class_exists($provider)) {
                continue;
            }
            $sdk = new $provider;
            if ($sdk instanceof PayAbstract) {
                $data[$key] = [
                    'key'      => $key,
                    'name'     => $provider::name(),
                    'form'     => $provider::config(),
                    'sdk'      => $provider,
                    'channels' => $provider::apis(),
                ];
            }
            unset($provider); //销毁对象
        }
        Cache::set($this->cache_provider_key, $data);
    }

    protected function getProviderCofig()
    {
        return Cache::get($this->cache_provider_key);
    }

    //重新缓存配置
    public function clearCache()
    {
        Cache::delete($this->cache_provider_key);
        $this->cacheProviderConfig();
        return true;
    }

    //获取所有支付平台，如果已经配置就会添加form_data
    public function platforms()
    {
        //数据库获取已配置数据
        $configs = PaymentConfig::all()->toArray();
        $configs = collect($configs)->keyBy('key');
        //配置文件中的所有平台
        $list      = [];
        $platforms = $this->getProviderCofig();
        foreach ($platforms as $key => $platform) {
            $data = [];
            if (isset($configs[$key])) {
                $data['data'] = unserialize($configs[$key]['config']);
                $data['id']   = $configs[$key]['id'];
            }
            $data['status'] = isset($configs[$key]['status']) && $configs[$key]['status'] == PaymentConfig::ON ? '开启' : '禁用';
            $list[$key] = array_merge($platform, $data);
        }
        return $list;
    }

    //获取所有支付平台，如果已经配置就会添加form_data
    public function platform($key)
    {
        //数据库获取已配置数据
        $config = PaymentConfig::where('key', $key)->first();
        if ($config) {
            $config->config = unserialize($config->config);
        }
        return $config;
    }

    //保存平台设置
    public function savePlatform()
    {
        $keys = array_keys($this->getProviderCofig());
        Validator::make(request()->all(), [
            'key'    => ['required', Rule::in($keys)],
            'status' => ['required', Rule::in(array_keys(PaymentConfig::STATUS))],
            //'sort'   => ['numeric'],
        ], [
            'key.required'    => '平台标识必传',
            'key.exists'      => '平台标识已经存在',
            'key.in'          => '平台标识不在可设置范围',
            'status.required' => '开关状态必传',
            'status.in'       => '开关状态不在可设置范围',
            'sort.numeric'    => '排序值必须是数字',
        ])->validate();
        $key = request('key');
        //验证key是否存在
        $model = PaymentConfig::where('key', $key)->first();
        if (!$model) {
            $model = new PaymentConfig();
        }
        //根据key找到必填项,并验证
        $providers  = $this->getProviderCofig();
        $attributes = $providers[$key]['form'] ?? [];
        $rule       = [];
        $message    = [];
        $config     = [];
        foreach ($attributes as $sign => $attribute) {
            $rule[$sign]                  = 'required';
            $message[$sign . '.required'] = $attribute . '必填';
            $config[$sign]                = request($sign);
        }
        Validator::make(request()->all(), $rule, $message)->validate();
        $model->key      = $key;
        $model->name     = $providers[$key]['name'] ?? '未知平台';
        $model->status   = request('status');
        $model->config   = serialize($config);
        $model->sort     = request('sort', 0);
        $model->callback = url($this->config['callback'], ['type' => $key]);
        if ($model->save()) {
            PaymentProvider::where('payment_config_id', $model->id)->update(['status' => request('status')]);
            $pids = PaymentProvider::where('payment_config_id', $model->id)->pluck('id')->toArray();
            PaymentPassageway::whereIn('pid',$pids)->where('table_type',PaymentPassageway::PAYMENT_PROVIDERS)->update(['status' => request('status')]);
            $this->clearCache();
            return true;
        }
        return false;
    }

    //获取支付类型
    public function tabs()
    {
        return PayTypes::TABS;
    }

    //根据充值类型获取已配置的通道
    public function channels()
    {
        $types = array_keys($this->tabs());
        Validator::make(request()->all(), [
            'type' => ['required', Rule::in($types)],
        ], [
            'type.required' => '支付类型必传',
            'type.in'       => '支付类型不存在'
        ])->validate();
        $type = request('type');
        return PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')
            ->leftJoin(PaymentProvider::tableName() . ' AS b', 'a.pid', '=', 'b.id')
            ->selectRaw('b.*,a.marker')->where('a.table_type',PaymentPassageway::PAYMENT_PROVIDERS)
            ->where('b.pay_type', $type)->orderBy('b.created_at', 'desc')->get();
    }

    //单个类型通道获取
    public function channel($id)
    {
        $list=[];
        $vip_lists= UserLevel::pluck('LevelName')->toArray();
        array_unshift($vip_lists, '未充值');
        if($id==0){
            $list['auths'] =  $vip_lists;
        }else {
            $list = PaymentProvider::from(PaymentProvider::tableName() . ' AS a')
                ->leftJoin(PaymentPassageway::tableName() . ' as b', function ($query) {
                    $query->on('b.pid', '=', 'a.id')->where('table_type',PaymentPassageway::PAYMENT_PROVIDERS);
                })->selectRaw('a.*, b.wid, b.sort,b.marker,b.authority,b.frequency')
                ->where('a.id',$id)
                ->first();
            $i = 0;
            $auths = [];
            foreach ($vip_lists as $k => $v) {
                $auths[$v] = $list->authority & pow(2, $i) ? 1 : 0;
                $i++;
            }
            $list->auths = $auths;
        }
        return  $list;
    }

    //根据类型获取可配通道
    public function canConfigChannels()
    {
        $types = array_keys($this->tabs());
        Validator::make(request()->all(), [
            'type' => ['required', Rule::in($types)],
        ], [
            'type.required' => '支付类型必传',
            'type.in'       => '支付类型不存在'
        ])->validate();
        $type      = request('type');
        $list      = [];
        $platforms = PaymentConfig::where('status', PaymentConfig::ON)->get();
        $providers = $this->getProviderCofig();
        foreach ($platforms as $key => $platform) {
          /*  $is_exit = PaymentProvider::where('payment_config_id', $platform->id)->where('pay_type',$type)->first();
            if(!$is_exit){*/
                $key       = $platform->key;
                if(!isset($providers[$key])){
                    continue;
                }
                $sdk_class = $providers[$key]['sdk'];
                $apis      = $sdk_class::apis();
                if (isset($apis[$type])) {
                    $list[$key]['name']      = $providers[$key]['name'];
                    $list[$key]['config_id'] = $platform->id;
                    $list[$key]['channels']  = $apis[$type];
                }
           // }
        }
        return $list;
    }

    public function order()
    {
        return $this->order;
    }

    // 添加支付通道
    public function saveChannel()
    {
        //验证key是否存在
        if (request('id')) {
            $model = PaymentProvider::find(request('id'));  //通道详情表
            $model_way = PaymentPassageway::where('table_type',PaymentPassageway::PAYMENT_PROVIDERS)->where('pid',request('id'))->first(); //关系表
            if (!$model && !$model_way) {
                throw new NotFoundHttpException('配置不存在');
            }
        } else {
            $model     = new PaymentProvider();
            $model_way = new PaymentPassageway();
        }
        $pay_value = '';
        if(request('pay_value')){
            $pay_value = request('pay_value');
            if(substr_count($pay_value,",") > 7){
                throw new NotFoundHttpException('固定面额最多只能配置8个');
            }
            //  去重 | 正序 | 限制8个
            $arr = array_slice(array_unique(explode(",", $pay_value)),0, 8);
            sort($arr);
            $pay_value = implode(",", $arr);
        }
        $providers = $this->getProviderCofig();
        $config    = PaymentConfig::find(request('payment_config_id'));
        $type      = request('pay_type');
        $channels  = $providers[$config->key]['channels'][$type] ?? [];
        if (!$config) {
            throw new NotFoundHttpException('配置不存在');
        }
        $types = array_keys($this->tabs());
        Validator::make(request()->all(), [
            'pay_type'          => ['required', Rule::in($types)],
            'payment_config_id' => ['required', Rule::exists($config->getTable(), 'id')],
            //'provider_key'      => ['required', Rule::in(array_keys($channels)), Rule::unique($model->getTable(), 'provider_key')->ignore(request('id'))],
            'provider_key'      => ['required', Rule::in(array_keys($channels))],
            'range'             => [Rule::in(['ON', 'OFF'])],
            'min_value'         => ['nullable','integer'],
            'max_value'         => ['nullable','integer'],
            'weight'            => ['integer'],
            'rate'              => ['nullable','numeric','max:999'],
            'wid'               => ['required'],
            'marker'            => ['required','in:0,1,2,3,4'],
            'sort'              => ['required','integer', 'min:1'],
            'status'            => ['required',Rule::in(['ON', 'OFF'])],
            'auths'             => ['required'],
            'frequency'         => ['required','numeric', 'min:0', 'max:999'],
        ], [
            'min_value.integer'     => '最小区间值必须是整数',
            //'min_value.min'         => '最小区间值必须大于等于1',
            'max_value.integer'     => '最大区间值必须是整数',
            //'max_value.min'         => '最大区间值必须大于等于1',
            'rate.integer'          => '费率必须是数字',
            'rate.max'              => '费率最大为999',
            'wid.required'          => '充值方式id必传',
            'marker.required'       => '角标类型必传',
            'marker.in'             => '角标类型不在可选范围',
            'sort.required'         => '排序值不可为空',
            'sort.integer'          => '排序值必须是整数',
            'sort.min'              => '排序值必须大于0',
            //'provider_key.unique'   => '充值通道已存在',
            'status.required'       => '通道开关必选',
            'status.in'             => '通道开关不在可选范围内',
            'auths.required'        => '权限必传',
            'frequency.required'  => '次数不能为空',
            'frequency.numeric'   => '次数必须为0~999之间的整数',
            'frequency.min'       => '次数必须为0~999之间的整数',
            'frequency.max'       => '次数必须为0~999之间的整数',
        ])->validate();
        if(request('range') == 'ON'){
            if(empty(request('min_value')) || empty(request('max_value'))){
                throw new NewException('区间值必须大于0');
            }
        }
        $model->payment_config_id = request('payment_config_id');
        $model->pay_type          = request('pay_type');
        $model->provider_key      = request('provider_key');
        $model->provider_name     = $channels[request('provider_key')] ?? '';
        $model->weight            = request('weight',0);
        $model->pay_value         = $pay_value;
        $model->range             = request('range', 'OFF');
        $model->min_value         = request('min_value') ?? 0;
        $model->max_value         = request('max_value') ?? 0;
        $model->rate              = request('rate', 100);
        $model->status            = request('status', PaymentPassageway::ON);
        if ($model->save()) {
            $model_way->pid           = $model->id;
            $model_way->table_type    = PaymentPassageway::PAYMENT_PROVIDERS;
            $model_way->wid           = request('wid');
            $model_way->name          = $channels[request('provider_key')] ?? '';
            $model_way->status        = request('status', PaymentPassageway::ON);
            $model_way->marker        = request('marker');
            $model_way->sort          = request('sort', 1) ;
            $model_way->frequency     = request('frequency', 0) ;
            $auths = request('auths',[]);
            if(count($auths) != count(PaymentPassageway::VIP_LISTS)){
                throw new NewException('传参有误');
            }
            $auth = 0;
            $i = 0;
            foreach ($auths as $k => $v){
                if ($v == 1){
                    $auth += pow(2,$i);
                }
                $i++;
            }
            $model_way->authority = $auth;
            if($model_way->save()){
                return true;
            }
        }
        return false;
    }
    //随机支付通道
    public function randomChannels()
    {
       /* $client_type = request('client_type','app');
        if ($client_type == 'pc'){
            $type_arr = [PayTypes::ALIPAY_QRCODE,PayTypes::WECHAT_QRCODE];
        }else{
            $type_arr = [PayTypes::ALIPAY_H5,PayTypes::WECHAT_H5];
        }*/
        $tabs = $this->tabs();
        $list = PaymentProvider::whereHas('config', function ($query) {
            $query->where('status', PaymentConfig::ON);
        })->where('status', PaymentProvider::ON)
            //->whereIn('pay_type',$type_arr)
            ->get()->toArray();
        if (count($list) <= 0) {
            return false;
        }
        $item = collect($list)->keyBy('id');
        $list = collect($list)->groupBy('pay_type');
        $data = [];
        foreach ($tabs as $type => $name) {
            if (isset($list[$type])) {
                $weight = collect($list[$type])->pluck('weight', 'id')->toArray();
                $id     = $this->random($weight);
                if (isset($item[$id])) {
                    $data[] = [
                        'name'          => $name,
                        'type'          => $type,
                        'provider_type' => $id,
                        'pay_value'     => array_map('intval', explode(',', $item[$id]['pay_value'])),
                        'range'         => $item[$id]['range'] == PaymentProvider::ON ? true : false,
                        'min'           => intval($item[$id]['min_value']),
                        'max'           => intval($item[$id]['max_value']),
                    ];
                }
            }
        }
        return $data;
    }
    //按权重随机命中支付
    //id=>weight
    private function random(array $weight)
    {
        $roll    = rand(1, array_sum($weight));
        $_tmpW   = 0;
        $rollnum = 0;
        foreach ($weight as $k => $v) {
            $min   = $_tmpW;
            $_tmpW += $v;
            $max   = $_tmpW;
            if ($roll > $min && $roll <= $max) {
                $rollnum = $k;
                break;
            }
        }
        return $rollnum;
    }

    //发起支付
    public function send()
    {
        Validator::make(request()->all(), [
            'user_id' => ['required', new UserExist('pay')],
            'money' => ['required', 'numeric', 'max:999999'],
            'type' => ['required', new ProviderExist()],//验证通道，金额等等
        ], [
            'user_id.required' => '用户标识不能为空',
            'money.required' => '充值金额不能为空',
            'money.numeric' => '充值金额必须是数字',
            'money.max' => '充值金额超过上限',
            'type.required' => '通道标识不能为空',
        ])->validate();
        try {
            //生成四方订单
            $model = new PaymentOrder();
            if (!$model->saveThirdOrder()) {
                return false;//此处不用记录日志，方法内部自带日志记录
            }
            $configs = $this->getProviderCofig();
            $key = $model->provider->config->key;
            $config = $this->getPlatformConfig($key);
            $sdk_class = $configs[$key]['sdk'];
            $sdk_object = new $sdk_class;
            if (!$config) {
                \Log::error($configs[$key]['name'] . '未配置');
                return false;
                //return $sdk_object->fail();
            }
            //获取对应的sdk类
            $sdk_object->setConfig($config);
            //调用sdk，发送支付请求，当发送请求失败后会执行此处代码
            if (!$sdk_object->send($model)){
                \Log::error('四方充值失败>>>' . $configs[$key]['name'] . '>>>>' . $model->return_data??'', []);
                $model->payment_status = PaymentOrder::FAILS;
                $model->save();//四方请求失败后，将订单改为失败状态
                return false;
            }
            // $model->return_data = serialize($sdk_object->returnData());
            if (!$model->save()) {
                return false;
            }
            $this->order = $model;
            return true;
        }catch (\Exception $exception){
            \Log::error('生成订单失败:' . $exception->getMessage());
            return false;
        }
    }


    //处理支付页面
    public function view($order_no)
    {
        $order = PaymentOrder::where('order_no', $order_no)->first();
        if (!$order) {
            return '订单找不到';
        }
        $key     = $order->provider->config->key;
        $configs = $this->getProviderCofig();
        if (isset($configs[$key]['sdk'])) {
            $sdk_class  = $configs[$key]['sdk'];
            $sdk_object = new $sdk_class;
            $config     = $this->getPlatformConfig($key);
            if (!$config) {
                \Log::error($configs[$key]['name'] . '未配置');
                return $sdk_object->fail();
            }
            $sdk_object->setConfig($config);
            return $sdk_object->view($order);
        }
        \Log::error('回调skd找不到');
        return '支付通道尚未接通';
    }

    //处理支付回调
    public function callback($sdk_name)
    {
        \Log::channel('callback')->info($_REQUEST);
        $configs = $this->getProviderCofig();
        if (isset($configs[$sdk_name]['sdk'])) {
            $sdk_class  = $configs[$sdk_name]['sdk'];
            $sdk_object = new $sdk_class;
            $config     = $this->getPlatformConfig($sdk_name);
            if (!$config) {
                \Log::error($configs[$sdk_name]['name'] . '未配置');
                return $sdk_object->fail();
            }
            $sdk_object->setConfig($config);
            if ($sdk_object->callback()) {
                return $sdk_object->success();
            }
            return $sdk_object->fail();
        }
        \Log::error('回调skd找不到');
        throw new NotFoundHttpException('页面不存在');
    }

    private function getPlatformConfig($key)
    {
        $config = PaymentConfig::where('key', $key)->first();
        if ($config) {
            $callback=url($this->config['callback'], ['type' => $key]);
            if(env("PAY_CALLBACK_URL")){
                $callback=env("PAY_CALLBACK_URL").'/'.$this->config['callback']."/".$key;
            }
            $arr=unserialize($config->config);
            $arr['callback'] = $callback;
            return $arr;
        }
        return false;
    }

    //支付通道获取，并重组
    public function payChannels()
    {
        $tabs = $this->tabs();
        $list = PaymentProvider::whereHas('config', function ($query) { $query->where('status', PaymentConfig::ON); })
            ->where('status', PaymentProvider::ON)->get()->groupBy('pay_type');
        $data = [];
        $i = 0;
        foreach ($tabs as $k => $v){
            $data[$i]['name']           = $v;
            $data[$i]['type']           = $k;
            $data[$i]['provider_type']  = 0;//0为默认不选择通道
            //固定值处理
            $money_arr = array_map('intval', explode(',', implode(',',$list[$k]->pluck('pay_value')->toArray())));
            $data[$i]['pay_value']      = array_unique($money_arr);
            //可选输入框处理，分组中只要有可选值，就可以打开
            $data[$i]['range']          = in_array(PaymentProvider::ON, $list[$k]->pluck('range')->toArray()) ? true : false;
            //最小值和最大值得重组换算
            $min_max_arr = array_unique(array_merge($list[$k]->pluck('min_value')->toArray(),$list[$k]->pluck('max_value')->toArray()));
            $data[$i]['min']            = min($min_max_arr);
            $data[$i]['max']            = max($min_max_arr);
            $i++;
        }
        return $data;
    }
    public function getSdk(){
        $key = $this->order->provider->config->key;
        $configs = $this->getProviderCofig();
        $config = $this->getPlatformConfig($key);
        $sdk_class = $configs[$key]['sdk'];
        $sdk_object = new $sdk_class;
        if (!$config) {
            \Log::error($configs[$key]['name'] . '未配置');
            return false;
            //return $sdk_object->fail();
        }
        //获取对应的sdk类
        $sdk_object->setConfig($config);
        return $sdk_object->sdk($this->order);
    }

}

<?php

namespace Modules\System\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\PaymentPassageway;
use Models\AdminPlatform\RechargeAgent;
use Models\AdminPlatform\RechargeAlipay;
use Models\AdminPlatform\RechargeUnion;
use Models\AdminPlatform\RechargeWechat;
use Transformers\RechargeWechatTransformer;
use Validator;

class OfficialPaymentController extends Controller
{
    public function wechat()
    {
        $list = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')->leftJoin(RechargeWechat::tableName() . ' AS b', 'a.pid', '=', 'b.id')
            ->selectRaw('b.*,a.marker')->where('a.table_type', PaymentPassageway::RECHARGE_WECHATS)
            ->orderBy('b.id', 'asc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RechargeWechatTransformer());
    }

    public function alipay()
    {
        $list = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')->leftJoin(RechargeAlipay::tableName() . ' AS b', 'a.pid', '=', 'b.id')
            ->selectRaw('b.*,a.marker')->where('a.table_type', PaymentPassageway::RECHARGE_ALIPAYS)
            ->orderBy('b.id', 'asc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RechargeWechatTransformer());
    }

    public function union()
    {
        $list = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')->leftJoin(RechargeUnion::tableName() . ' AS b', 'a.pid', '=', 'b.id')
            ->selectRaw('b.*,a.marker')->where('a.table_type', PaymentPassageway::RECHARGE_UNIONS)
            ->orderBy('b.id', 'asc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RechargeWechatTransformer());
    }

    public function bank()
    {
        return ResponeSuccess('查询成功', RechargeUnion::BANK);
    }

    public function numberType()
    {
        return ResponeSuccess('查询成功', RechargeAgent::TYPE);
    }

    public function agent()
    {
        $list = PaymentPassageway::from(PaymentPassageway::tableName() . ' AS a')->leftJoin(RechargeAgent::tableName() . ' AS b', 'a.pid', '=', 'b.id')
            ->selectRaw('b.*,a.marker')->where('a.table_type', PaymentPassageway::RECHARGE_AGENTS)
            ->orderBy('b.id', 'asc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new RechargeWechatTransformer());
    }

    public function saveWechat(Request $request)
    {
        Validator::make($request->all(), [
            'name'         => ['required'],
            'image'        => ['image'],
            //'code_address' => ['required_without:id', 'file', 'image'],
            'code_address' => ['required_without:id'],
            'nickname'     => ['required'],
            'sort'         => ['integer','min:0','max:9999999'],
            'ratio'        => ['numeric','min:0','max:100'],
            'state'        => ['in:0,1'],
        ], [
            'name.required'                 => '名称必填',
            'code_address.required_without' => '二维码图片必传',
            'image.image'                   => '二维码图片类型不正确',
            'nickname.required'             => '昵称必填',
            'sort.interge'                  => '排序值必须是整数',
            'sort.min'                      => '排序值最小值为0',
            'sort.max'                      => '排序值最大值为9999999',
            'ratio.numeric'                 => '充值比率必须是数字',
            'ratio.min'                     => '充值比率最小值为0',
            'ratio.max'                     => '充值比率最大值为100',
            'state.in'                      => '状态值不在可选范围',
        ])->validate();
        if ($request->input('id')) {
            $model = RechargeWechat::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值方式不存在');
            }
            //echo asset('storage/'.$model->code_address);
        } else {
            $model = new RechargeWechat();
        }
        if (isset($_FILES['image']) && $request->file('image')->isValid()) {
            $path                = $request->image->store('qrcode_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'qrcode_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            $model->code_address = $path;
        } else {
            $model->code_address = $request->input('code_address');
        }
        $model->name     = $request->input('name');
        $model->nickname = $request->input('nickname');
        $model->ratio    = $request->input('ratio', 0);
        $model->sort     = $request->input('sort', 0);
        $model->state    = $request->input('state', RechargeWechat::OFF);
        if ($model->save()) {
            if(!$request->input('id')){
                $data = new PaymentPassageway();
                $data -> pid = $model->id;
                $data -> table_type = PaymentPassageway::RECHARGE_WECHATS;
                $data -> wid = PaymentPassageway::RECHARGE_WECHATS;
                $data -> status = PaymentPassageway::OFF;
                $data -> name = $request->input('name');
                if($data->save()){
                    return ResponeSuccess('保存成功');
                }else{
                    return ResponeFails('保存失败');
                }
            } else{
                return ResponeSuccess('保存成功');
            }
        }
        return ResponeFails('保存失败');
    }

    public function saveAlipay(Request $request)
    {
        Validator::make($request->all(), [
            'name'         => ['required'],
            'image'        => ['file', 'image'],
            //'code_address' => ['required_without:id', 'file', 'image'],
            'code_address' => ['required_without:id'],
            'nickname'     => ['required'],
            'sort'         => ['integer','min:0','max:9999999'],
            'ratio'        => ['numeric','min:0','max:100'],
            'state'        => ['in:0,1'],
        ], [
            'name.required'                 => '名称必填',
            'code_address.required_without' => '二维码图片必传',
            'image.image'                   => '二维码图片类型不正确',
            'nickname.required'             => '昵称必填',
            'sort.interge'                  => '排序值必须是整数',
            'sort.min'                      => '排序值最小值为0',
            'sort.max'                      => '排序值最大值为9999999',
            'ratio.numeric'                 => '充值比率必须是数字',
            'ratio.min'                     => '充值比率最小值为0',
            'ratio.max'                     => '充值比率最大值为100',
            'state.in'                      => '状态值不在可选范围',
        ])->validate();
        if ($request->input('id')) {
            $model = RechargeAlipay::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值方式不存在');
            }
            //echo asset('storage/'.$model->code_address);
        } else {
            $model = new RechargeAlipay();
        }
        if (isset($_FILES['image']) && $request->file('image')->isValid()) {
            $path                = $request->image->store('qrcode_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'qrcode_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            $model->code_address = $path;
        } else {
            $model->code_address = $request->input('code_address');
        }
        $model->name     = $request->input('name');
        $model->nickname = $request->input('nickname');
        $model->ratio    = $request->input('ratio', 0);
        $model->sort     = $request->input('sort', 0);
        $model->state    = $request->input('state', RechargeAlipay::OFF);
        if ($model->save()) {
            if(!$request->input('id')){
                $data = new PaymentPassageway();
                $data -> pid = $model->id;
                $data -> table_type = PaymentPassageway::RECHARGE_ALIPAYS;
                $data -> wid = PaymentPassageway::RECHARGE_ALIPAYS;
                $data -> status = PaymentPassageway::OFF;
                $data -> name = $request->input('name');
                if($data->save()){
                    return ResponeSuccess('保存成功');
                }else{
                    return ResponeFails('保存失败');
                }
            } else{
                return ResponeSuccess('保存成功');
            }
        }
        return ResponeFails('保存失败');
    }

    public function saveUnion(Request $request)
    {
        Validator::make($request->all(), [
            'name'         => ['required'],
            'bank_id'      => ['required', Rule::in(array_keys(RechargeUnion::BANK))],
            'payee'        => ['required'],
            'card_number'  => ['required','numeric'],
            'opening_bank' => ['required'],
            'ratio'        => ['numeric','min:0','max:100'],
            'sort'         => ['integer','min:0','max:9999999'],
            'state'        => ['in:0,1'],
        ], [
            'name.required'         => '名称必填',
            'bank_id.required'      => '银行id必选',
            'bank_id.in'            => '银行id不在选择范围',
            'payee.required'        => '收款人必传',
            'card_number.required'  => '卡号必传',
            'opening_bank.required' => '开户行必传',
            'sort.interge'          => '排序值必须是整数',
            'sort.min'              => '排序值最小值为0',
            'sort.max'              => '排序值最大值为9999999',
            'ratio.numeric'         => '充值比率必须是数字',
            'ratio.min'             => '充值比率最小值为0',
            'ratio.max'             => '充值比率最大值为100',
            'state.in'              => '状态值不在可选范围',
        ])->validate();
        if ($request->input('id')) {
            $model = RechargeUnion::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值方式不存在');
            }
        } else {
            $model = new RechargeUnion();
        }

        $model->name         = $request->input('name');
        $model->bank_id      = $request->input('bank_id');
        $model->payee        = $request->input('payee');
        $model->card_number  = $request->input('card_number');
        $model->opening_bank = $request->input('opening_bank');
        $model->ratio        = $request->input('ratio', 0);
        $model->sort         = $request->input('sort', 0);
        $model->state        = $request->input('state', RechargeUnion::OFF);
        if ($model->save()) {
            if(!$request->input('id')){
                $data = new PaymentPassageway();
                $data -> pid = $model->id;
                $data -> table_type = PaymentPassageway::RECHARGE_UNIONS;
                $data -> wid = PaymentPassageway::RECHARGE_UNIONS;
                $data -> status = PaymentPassageway::OFF;
                $data -> name = $request->input('name');
                if($data->save()){
                    return ResponeSuccess('保存成功');
                }else{
                    return ResponeFails('保存失败');
                }
            } else{
                return ResponeSuccess('保存成功');
            }
        }
        return ResponeFails('保存失败');
    }

    public function saveAgent(Request $request)
    {
        Validator::make($request->all(), [
            'name'        => ['required'],
            'number_type' => ['required', Rule::in(array_keys(RechargeAgent::TYPE))],
            'number'      => ['required','numeric'],
            'nickname'    => ['required'],
            'sort'        => ['integer','min:0','max:9999999'],
            'state'       => ['in:0,1'],
        ], [
            'name.required'        => '名称必填',
            'number_type.required' => '号码类型必选',
            'number_type.in'       => '号码类型不在可选范围',
            'number.required'      => '号码必填',
            'number.numeric'       => '号码必须是数字',
            'nickname.required'    => '昵称必填',
            'sort.interge'         => '排序值必须是整数',
            'sort.min'             => '排序值最小值为0',
            'sort.max'             => '排序值最大值为9999999',
            'state.in'             => '状态值不在可选范围',
        ])->validate();
        if ($request->input('id')) {
            $model = RechargeAgent::find($request->input('id'));
            if (!$model) {
                return ResponeFails('充值方式不存在');
            }
        } else {
            $model = new RechargeAgent();
        }
        $model->name        = $request->input('name');
        $model->number_type = $request->input('number_type');
        $model->number      = $request->input('number');
        $model->nickname    = $request->input('nickname');
        $model->sort        = $request->input('sort', 0);
        $model->state       = $request->input('state', RechargeAgent::OFF);
        if ($model->save()) {
            if(!$request->input('id')){
                $data = new PaymentPassageway();
                $data -> pid = $model->id;
                $data -> table_type = PaymentPassageway::RECHARGE_AGENTS;
                $data -> wid = PaymentPassageway::RECHARGE_AGENTS;
                $data -> status = PaymentPassageway::OFF;
                $data -> name = $request->input('name');
                if($data->save()){
                    return ResponeSuccess('保存成功');
                }else{
                    return ResponeFails('保存失败');
                }
            } else{
                return ResponeSuccess('保存成功');
            }
        }
        return ResponeFails('保存失败');
    }
    //启用
    public function changeStatusOn(Request $request)
    {
        Validator::make($request->all(), [
            'ids'  => ['required', 'Array'],
            'type' => ['required', 'in:1,2,3,4'],
        ], [
            'ids.required' => '缺少参数',
            'ids.Array'    => 'ids参数必须是数组',
            'type.in'      => '类型不在范围内',
        ])->validate();
        switch ($request->input('type')) {
            case 1:
                RechargeWechat::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::ON]);
                $table_type = 3;
                break;
            case 2:
                RechargeAlipay::whereIn('id', $request->input('ids'))->update(['state' => RechargeAlipay::ON]);
                $table_type = 4;
                break;
            case 3:
                RechargeUnion::whereIn('id', $request->input('ids'))->update(['state' => RechargeUnion::ON]);
                $table_type = 2;
                break;
            case 4:
                RechargeAgent::whereIn('id', $request->input('ids'))->update(['state' => RechargeAgent::ON]);
                $table_type = 1;
                break;
        }
        PaymentPassageway::whereIn('pid', $request->input('ids'))->where('table_type',$table_type)->update(['status' => 'ON']);
        return ResponeSuccess('启用成功');

    }
    //删除
    public function changeStatusDel(Request $request)
    {
        Validator::make($request->all(), [
            'ids'  => ['required', 'Array'],
            'type' => ['required', 'in:1,2,3,4'],
        ], [
            'ids.required' => '缺少参数',
            'ids.Array'    => 'ids参数必须是数组',
            'type.in'      => '类型不在范围内',
        ])->validate();
        switch ($request->input('type')) {
            case 1:
                RechargeWechat::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::OFF]);
                $table_type = 3;
                break;
            case 2:
                RechargeAlipay::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::OFF]);
                $table_type = 4;
                break;
            case 3:
                RechargeUnion::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::OFF]);
                $table_type = 2;
                break;
            case 4:
                RechargeAgent::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::OFF]);
                $table_type = 1;
                break;
        }
        PaymentPassageway::whereIn('pid',$request->input('ids'))->where('table_type', $table_type)->delete();
        return ResponeSuccess('删除成功');
    }
    //禁用
    public function changeStatusOff(Request $request)
    {
        Validator::make($request->all(), [
            'ids'  => ['required', 'Array'],
            'type' => ['required', 'in:1,2,3,4'],
        ], [
            'ids.required' => '缺少参数',
            'ids.Array'    => 'ids参数必须是数组',
            'type.in'      => '类型不在范围内',
        ])->validate();
        switch ($request->input('type')) {
            case 1:
                RechargeWechat::whereIn('id', $request->input('ids'))->update(['state' => RechargeWechat::OFF]);
                $table_type = 3;
                break;
            case 2:
                RechargeAlipay::whereIn('id', $request->input('ids'))->update(['state' => RechargeAlipay::OFF]);
                $table_type = 4;
                break;
            case 3:
                RechargeUnion::whereIn('id', $request->input('ids'))->update(['state' => RechargeUnion::OFF]);
                $table_type = 2;
                break;
            case 4:
                RechargeAgent::whereIn('id', $request->input('ids'))->update(['state' => RechargeAgent::OFF]);
                $table_type = 1;
                break;
        }
        PaymentPassageway::whereIn('pid', $request->input('ids'))->where('table_type',$table_type)->update(['status' => 'OFF']);
        return ResponeSuccess('禁用成功');

    }

    public function upload(Request $request)
    {
        Validator::make($request->all(), [
            'image' => ['required', 'file', 'image'],
        ], [
            'image.required' => '二维码图片必传',
            'image.image'    => '二维码图片类型不正确',
        ])->validate();
        if ($request->file('image')->isValid()) {
            $path = $request->image->store('qrcode_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'qrcode_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            return ResponeSuccess('上传成功', ['path' => $path]);
        }
        return ResponeFails('上传失败');
    }

}

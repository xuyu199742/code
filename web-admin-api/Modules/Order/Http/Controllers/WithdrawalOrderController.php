<?php

/**
 *
 */

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectGameIdRequest;
use App\Jobs\SendMailUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\OrderLog;
use Models\AdminPlatform\RemitConfig;
use Models\AdminPlatform\SystemSetting;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;
use Modules\Order\Packages\lib\ChangRemit;
use Modules\Order\Packages\lib\LXRemit;
use Modules\Order\Packages\lib\XinRemit;
use Transformers\WithdrawalOrderTransformer;
use Validator;

class WithdrawalOrderController extends Controller
{

    /**
     * 订单列表
     *
     * @param Request $request
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(WithdrawalOrder::STATUS))],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => 'game_id必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_time.date'        => '无效日期',
            'end_time.date'          => '无效日期',
        ])->validate();
        $search_time      = $request->input('time_type', 1) == 1 ? 'created_at' : 'complete_time';
        $data = WithdrawalOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->andFilterWhere('order_no', $request->input('order_no'))
            ->orderBy($search_time, 'DESC');
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = $statistics_coins->sum('money');
        return $this->response->paginator($list, new WithdrawalOrderTransformer())->addMeta('status',WithdrawalOrder::STATUS)
            ->addMeta('count',$count);
    }

    /**
     * 财务订单列表
     *
     * @param Request $request
     *
     * @return \Dingo\Api\Http\Response
     */
    public function financeList(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_merge(array_keys(WithdrawalOrder::FINANCE_STATUS),array_keys(WithdrawalAutomatic::SUBSET_STATUS_ALIAS)))],
            'third_order_no' =>['nullable'],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => 'game_id必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_time.date'        => '无效日期',
            'end_time.date'          => '无效日期',
        ])->validate();
        $search_time      = $request->input('time_type', 1) == 1 ? 'created_at' : 'complete_time';
        $data=WithdrawalOrder::whereHas('withdrawalAuto', function($query)use ($request){
            if($request->input('third_order_no','')){
                $query->where('third_order_no', $request->input('third_order_no',''));
            }
            $status=$request->input('status');
            if(in_array($status,array_keys(WithdrawalAutomatic::SUBSET_STATUS_ALIAS))){
                $real_status=WithdrawalAutomatic::SUBSET_STATUS_ALIAS[$request->input('status')];
                if($real_status==WithdrawalAutomatic::LOCK){  //子状态为锁定时
                    $query->where('lock_id', '>',0);
                }else{
                    $query->where('withdrawal_status', $real_status);
                }
            }
        })->whereIn('status', array_keys(WithdrawalOrder::FINANCE_STATUS))
            ->andFilterWhere('game_id', $request->input('game_id'));
        $status=$request->input('status');
        if(in_array($status,array_keys(WithdrawalOrder::FINANCE_STATUS))){
            $data->andFilterWhere('status', $request->input('status'));
        }else{
            if(in_array($status,array_keys(WithdrawalAutomatic::SUBSET_STATUS_ALIAS))){
                $real_status=WithdrawalAutomatic::SUBSET_STATUS_ALIAS[$request->input('status')];
                //订单审核状态的子状态
                if(in_array($real_status,array_keys(WithdrawalAutomatic::FINANCE_WAIT))){
                    $data->andFilterWhere('status', WithdrawalOrder::CHECK_PASSED);
                }
                //订单成功的子状态
                if(in_array($real_status,array_keys(WithdrawalAutomatic::PAYMENT_SUCCESS))){
                    $data->andFilterWhere('status', WithdrawalOrder::PAY_SUCCESS);
                }
                //订单失败的子状态
                if(in_array($real_status,array_keys(WithdrawalAutomatic::PAYMENT_FAILS))){
                    $data->andFilterWhere('status', WithdrawalOrder::PAY_FAILS);
                }
            }
        }
        $data->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->andFilterWhere('order_no', $request->input('order_no'))
            ->orderBy($search_time, 'DESC');
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = $statistics_coins->sum('money');
        return $this->response->paginator($list, new WithdrawalOrderTransformer())->addMeta('status',WithdrawalOrder::FINANCE_STATUS)->addMeta('sub_status',WithdrawalOrder::subStatus())
            ->addMeta('count',$count);
    }

    // 客服处理订单 通过
    public function servicesOrderPassed(Request $request)
    {
        try {
            $withdrawalOrder = WithdrawalOrder::find($request->input('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($withdrawalOrder->status != WithdrawalOrder::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        $withdrawalOrder->setAttribute('status', WithdrawalOrder::CHECK_PASSED);
        $withdrawalOrder->admin_id = $this->user()->id;
        $withdrawalOrder->remark = $request->input('remark','');

        try{
            \DB::beginTransaction();
            //客服审核通过更改订单状态
            $res1 = $withdrawalOrder->save();
            //订单子状态改为人工出款失败（需要生成关联的数据）
            $res2 = WithdrawalAutomatic::saveOne($withdrawalOrder->id);
            if ($res1 && $res2){
                \DB::commit();
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $withdrawalOrder->order_no . '并通过', $withdrawalOrder->order_no, config('set.withdrawal').'订单');
                return ResponeSuccess('审核成功');
            }else{
                \DB::rollBack();
                return ResponeFails('审核失败');
            }
        }catch (\Exception $e){
            \DB::rollBack();
            return ResponeFails('审核失败,请联系管理员');
        }
    }

    // 客服处理订单 不通过
    public function servicesOrderFails(Request $request)
    {
        try {
            $withdrawalOrder = WithdrawalOrder::find($request->input('id'));
        } catch (\ErrorException $exception) {
            return ResponeFails('订单查找不到');
        }
        if ($withdrawalOrder->status != WithdrawalOrder::WAIT_PROCESS) {
            return ResponeFails('订单状态已更新,无法进行操作');
        }
        if ($withdrawalOrder->rollBackCoins()) {
            try{
                $withdrawalOrder->remark = $request->input('remark','');
                $withdrawalOrder->admin_id = $this->user()->id;
                $withdrawalOrder->save();
            }catch (\Exception $e){

            }
            $sendType=$request->input('send_type');
            if($sendType==1) //发送邮件
            {
                $data=[];
                $data['GameIDs']=[$withdrawalOrder->game_id];
                $data['SendType']='1';   // 按玩家发送
                $data['Title']='客服审核订单-取消订单';  //邮件标题
                $data['Context']=$request->input('remark');  //邮件内容
                $data['TimeType']='1';  //发送类型：1、立即发送，2、定时发送
                $data['admin_id']=$this->user()->id;   //发送人
                $res = SendMailUser::dispatch($data);
                if ($res){
                    return ResponeSuccess('发送成功');
                }else{
                    return ResponeFails('发送失败');
                }
            }
            OrderLog::addLogs($this->user()->username . '审核了订单:' . $withdrawalOrder->order_no . '并不通过,金币返还', $withdrawalOrder->order_no, config('set.withdrawal').'订单');
            return ResponeSuccess('审核成功,用户金币已返还');
        }
        return ResponeFails('审核失败,请联系管理员');
    }
    /*
    * 财务处理订单 锁定
    * */
    public function financeOrderLock(Request $request)
    {
        try {
            \DB::beginTransaction();
            $withdrawalOrder = WithdrawalOrder::where('id',$request->input('id'))->lockForUpdate()->first();
            if (!$withdrawalOrder){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',$request->input('id'))->lockForUpdate()->first();
            if (!$WithdrawalAutomatic){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            /*主状态判断*/
            if ($withdrawalOrder->status != WithdrawalOrder::CHECK_PASSED) {
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*子状态判断*/
            $sub_status=[WithdrawalAutomatic::FINANCE_CHECK,WithdrawalAutomatic::AUTOMATIC_FAILS];
            if (!in_array($WithdrawalAutomatic->withdrawal_status,$sub_status)){
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            $is_lock=$WithdrawalAutomatic->lock_id;
            $admin_id = $this->user()->id;
            if($is_lock>0 && $is_lock!=$admin_id)
            {
                \DB::rollBack();
                return ResponeFails('该订单已被锁定,无法进行操作');
            }
        } catch (\ErrorException $exception) {
            \DB::rollBack();
            return ResponeFails('订单查询异常');
        }
        try{

            $WithdrawalAutomatic->lock_id = $admin_id; //添加锁定人
            $res = $WithdrawalAutomatic->save();
            if ($res){
                \DB::commit();
                OrderLog::addLogs($this->user()->username . '锁定了订单:' . $withdrawalOrder->order_no . '并且其他管理员不能对订单', $withdrawalOrder->order_no, '进行操作');
                return ResponeSuccess('订单锁定成功');
            }else{
                \DB::rollBack();
                return ResponeFails('订单锁定失败');
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            return ResponeFails('订单锁定失败');
        }
    }
    /*
    * 财务处理订单 确定(人工出款成功)
    * */
    public function financeOrderPassed(Request $request)
    {
        try {
            \DB::beginTransaction();
            $withdrawalOrder = WithdrawalOrder::where('id',$request->input('id'))->lockForUpdate()->first();
            if (!$withdrawalOrder){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',$request->input('id'))->lockForUpdate()->first();
            if (!$WithdrawalAutomatic){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            /*主状态判断*/
            if ($withdrawalOrder->status != WithdrawalOrder::CHECK_PASSED) {
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*子状态判断*/
            $sub_status=[WithdrawalAutomatic::FINANCE_CHECK,WithdrawalAutomatic::AUTOMATIC_FAILS];
            if (!in_array($WithdrawalAutomatic->withdrawal_status,$sub_status)){
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*判断当前子状态是否允许被修改*/
            $is_lock=$WithdrawalAutomatic->lock_id;
            $admin_id = $this->user()->id;
            if($is_lock>0 && $is_lock!=$admin_id)
            {
                \DB::rollBack();
                return ResponeFails('该订单已被锁定,无法进行操作');
            }
        } catch (\ErrorException $exception) {
            \DB::rollBack();
            return ResponeFails('订单查询异常');
        }
        $withdrawalOrder->setAttribute('status', WithdrawalOrder::PAY_SUCCESS);
        $withdrawalOrder->admin_id = $admin_id;
        $withdrawalOrder->complete_time = date('Y-m-d H:i:s');
        $res1 = $withdrawalOrder->save();
        $res2 = WithdrawalAutomatic::where('order_id',$request->input('id'))->update(['withdrawal_status'=>WithdrawalAutomatic::ARTIFICIAL_SUCCESS]);
        if ($res1 && $res2) {
            //清空用户当日打码量
            GameScoreInfo::where('UserID',$withdrawalOrder->user_id)->update(['CurJettonScore'=>0]);
            \DB::commit();
            OrderLog::addLogs($this->user()->username . '审核了订单:' . $withdrawalOrder->order_no . '并通过', $withdrawalOrder->order_no, config('set.withdrawal').'订单');
            return ResponeSuccess('审核成功');
        }
        \DB::rollBack();
        return ResponeFails('审核失败,请联系管理员');
    }
    /*
    * 财务处理订单 不通过，人工出款失败(取消按钮-用户金币返还)
    * */
    public function financeOrderFails(Request $request)
    {
        try {
            \DB::beginTransaction();
            $withdrawalOrder = WithdrawalOrder::where('id',$request->input('id'))->lockForUpdate()->first();
            if (!$withdrawalOrder){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',$request->input('id'))->lockForUpdate()->first();
            if (!$WithdrawalAutomatic){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            /*主状态判断*/
            if ($withdrawalOrder->status != WithdrawalOrder::CHECK_PASSED) {
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*子状态判断*/
            $sub_status=[WithdrawalAutomatic::FINANCE_CHECK,WithdrawalAutomatic::AUTOMATIC_FAILS];
            if (!in_array($WithdrawalAutomatic->withdrawal_status,$sub_status)){
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*判断当前子状态是否允许被修改*/
            $is_lock=$WithdrawalAutomatic->lock_id;
            $admin_id = $this->user()->id;
            if($is_lock>0 && $is_lock!=$admin_id)
            {
                \DB::rollBack();
                return ResponeFails('该订单已被锁定,无法进行操作');
            }
        } catch (\Exception $exception) {
            \DB::rollBack();
            return ResponeFails('订单查询异常');
        }
        //失败返回金币
        if ($withdrawalOrder->rollBackCoins()) {
            try{
                //填写拒绝原因，并更改订单状态
                $withdrawalOrder->remark = $request->input('remark','');
                $withdrawalOrder->status = WithdrawalOrder::PAY_FAILS;
	            //$withdrawalOrder->complete_time = date('Y-m-d H:i:s');
                $withdrawalOrder->admin_id = $this->user()->id;
                $res1 = $withdrawalOrder->save();
                //订单子状态改为人工出款失败（需要生成关联的数据）
                $res2 = WithdrawalAutomatic::where('order_id',$request->input('id'))->update(['withdrawal_status'=>WithdrawalAutomatic::ARTIFICIAL_FAILS]);
                //审核备注，发送邮件
                $sendType=$request->input('send_type');
                if ($res1 && $res2){
                    \DB::commit();
                    if($sendType==1) //发送邮件
                    {
                        $data=[];
                        $data['GameIDs']=[$withdrawalOrder->game_id];
                        $data['SendType']='1';   // 按玩家发送
                        $data['Title']='财务审核订单-取消订单';  //邮件标题
                        $data['Context']=$request->input('remark');  //邮件内容
                        $data['TimeType']='1';  //发送类型：1、立即发送，2、定时发送
                        $data['admin_id']=$this->user()->id;   //发送人
                        try{
                            $res = SendMailUser::dispatch($data);
                            if ($res){
                                return ResponeSuccess('发送成功');
                            }else{
                                return ResponeFails('发送失败');
                            }
                        }catch (\Exception $e){
                            return ResponeFails('订单修改成功，发送邮件失败');
                        }

                    }
                }else{
                    \DB::rollBack();
                    return ResponeFails('审核失败,请联系管理员');
                }
            }catch (\Exception $e){
                \DB::rollBack();
                return ResponeFails('审核失败,请联系管理员');
            }
            \DB::rollBack();
            OrderLog::addLogs($this->user()->username . '审核了订单:' . $withdrawalOrder->order_no . '并不通过,金币返还', $withdrawalOrder->order_no, config('set.withdrawal').'订单');
            return ResponeSuccess('审核成功,用户金币已返还');
        }

        return ResponeFails('审核失败,请联系管理员');
    }
    /*
     * 财务处理订单 不通过，人工出款失败(拒绝按钮-用户金币扣除)
     * */
    public function financeOrderRefuse(Request $request)
    {
        \DB::beginTransaction();
        $order_id = $request->input('id');
        try {
            $withdrawalOrder = WithdrawalOrder::where('id',$order_id)->lockForUpdate()->first();
            if (!$withdrawalOrder){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',$order_id)->lockForUpdate()->first();
            if (!$WithdrawalAutomatic){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            /*主状态判断*/
            if ($withdrawalOrder->status != WithdrawalOrder::CHECK_PASSED) {
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*子状态判断*/
            $sub_status=[WithdrawalAutomatic::FINANCE_CHECK,WithdrawalAutomatic::AUTOMATIC_FAILS];
            if (!in_array($WithdrawalAutomatic->withdrawal_status,$sub_status)){
                \DB::rollBack();
                return ResponeFails('订单状态已更新,无法进行操作');
            }
            /*判断当前子状态是否允许被修改*/
            $is_lock=$WithdrawalAutomatic->lock_id;
            $admin_id = $this->user()->id;
            if($is_lock>0 && $is_lock!=$admin_id)
            {
                \DB::rollBack();
                return ResponeFails('该订单已被锁定,无法进行操作');
            }
        } catch (\ErrorException $exception) {
            \DB::rollBack();
            return ResponeFails('订单查询异常');
        }
        try{
            //填写拒绝原因，并更改订单状态
            $withdrawalOrder->remark    = $request->input('remark','');
            $withdrawalOrder->status    = WithdrawalOrder::PAY_FAILS;
	        //$withdrawalOrder->complete_time = date('Y-m-d H:i:s');
            $withdrawalOrder->admin_id  = $admin_id;
            $res1 = $withdrawalOrder->save();
            //订单子状态改为人工出款失败（需要生成关联的数据）
            $res2 = WithdrawalAutomatic::where('order_id',$order_id)->update(['withdrawal_status'=>WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE]);
            //写流水记录
            $score = GameScoreInfo::where('UserID', $withdrawalOrder->user_id)->first();
            if (!$score) {
                \Log::error('玩家已经不存在,用户id' . $withdrawalOrder->user_id . '游戏id:' . $withdrawalOrder->game_id);
                return false;
            }
            $localcoin = $score->Score;
            $record_treasure_serial = new RecordTreasureSerial();
            $res3 = $record_treasure_serial->addRecord($withdrawalOrder->user_id, $localcoin, $score->InsureScore, -((int)$withdrawalOrder->real_gold_coins), RecordTreasureSerial::WITHDRAWAL_REFUSE, $admin_id,'',$withdrawalOrder->id);
            //审核备注，发送邮件
            $sendType=$request->input('send_type');
            if ($res1 && $res2 && $res3){
                \DB::commit();
                if($sendType==1) //发送邮件
                {
                    $data = [];
                    $data['GameIDs'] = [$withdrawalOrder->game_id];
                    $data['SendType'] = '1';   // 按玩家发送
                    $data['Title'] = '财务审核订单-拒绝订单';  //邮件标题
                    $data['Context'] = $request->input('remark');  //邮件内容
                    $data['TimeType'] = '1';  //发送类型：1、立即发送，2、定时发送
                    $data['admin_id'] = $this->user()->id;   //发送人
                    try{
                        $res = SendMailUser::dispatch($data);
                        if ($res) {
                            return ResponeSuccess('发送成功');
                        } else {
                            return ResponeFails('发送失败');
                        }
                    }catch (\Exception $e){
                        return ResponeFails('订单修改成功，发送邮件失败');
                    }
                }
                OrderLog::addLogs($this->user()->username . '审核了订单:' . $withdrawalOrder->order_no . '拒绝金币不返还', $withdrawalOrder->order_no, config('set.withdrawal').'订单');
                return ResponeSuccess('审核成功,用户金币不返还');
            }else{
                \DB::rollBack();
                return ResponeFails('审核失败,请联系管理员');
            }
        }catch (\Exception $exception){
            \DB::rollBack();
            return ResponeFails('审核失败,请联系管理员');
        }
    }

    /*
     * 自动打款
     *
     */
    public function financeOrderAutomatic(Request $request)
    {
        //查询订单
        try {
            \DB::beginTransaction();
            $withdrawalOrder = WithdrawalOrder::where('id',request('id'))->lockForUpdate()->first();
            if (!$withdrawalOrder){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',request('id'))->lockForUpdate()->first();
            if (!$WithdrawalAutomatic){
                \DB::rollBack();
                return ResponeFails('订单查找不到');
            }
            //判断主状态是否为财务待审核
            if ($withdrawalOrder->status != WithdrawalOrder::CHECK_PASSED) {
                \DB::rollBack();
                return ResponeFails('订单只有财务待审核才能进行该操作');
            }
            //如果为锁定状态只允许本人修改
            if ($WithdrawalAutomatic->lock_id != 0 && $WithdrawalAutomatic->lock_id != $this->user()->id){
                \DB::rollBack();
                return ResponeFails('订单被锁定，只能锁定人进行操作');
            }
            $WithdrawalAutomatic->withdrawal_status=WithdrawalAutomatic::AUTOMATIC_PAYMENT;
            if(!$WithdrawalAutomatic->save()){
                \DB::rollBack();
                return ResponeFails('自动打款失败');
            }
            //通道选择
            $RemitConfig = RemitConfig::where('status',RemitConfig::STATUS_ON)->first();
            if (empty($RemitConfig)){
                \DB::rollBack();
                return ResponeFails('配置不存在或未开启');
            }
            //判断打款是否在范围内
            if ($withdrawalOrder->money < $RemitConfig->min_money || $withdrawalOrder->money > $RemitConfig->max_money){
                \DB::rollBack();
                return ResponeFails('打款'.config('set.amount').'不在范围内，最低50，最多45000');
            }
            \DB::commit();
        } catch (\Exception $exception) {
            return ResponeFails('订单查询异常');
        }

        $config['mch_id']       = $RemitConfig->mch_id;
        $config['mch_key']      = $RemitConfig->mch_key;
        $config['order_url']    = $RemitConfig->gateway;//网关下单地址
        $config['notify_url']   = url('api/payment/remit/'.$RemitConfig->notify_tag);//回调地址
        $BankPay = new $RemitConfig->sdk($config);
        return $BankPay->remit($withdrawalOrder);
    }

    /**
     * 自动出款中改为自动出款失败
     *
     */
    public function autoFailed()
    {
        $withdrawalOrder = WithdrawalOrder::where('id',intval(request('id')))->first();
        if (!$withdrawalOrder){
            return ResponeFails('订单查找不到');
        }
        $WithdrawalAutomatic = WithdrawalAutomatic::where('order_id',intval(request('id')))->first();
        if (!$WithdrawalAutomatic){
            return ResponeFails('订单查找不到');
        }
        //如果为锁定状态只允许本人修改
        if ($WithdrawalAutomatic->lock_id != 0 && $WithdrawalAutomatic->lock_id != $this->user()->id){
            return ResponeFails('订单被锁定，只能锁定人进行操作');
        }
        //判断子状态是否自动出款中
        if ($WithdrawalAutomatic->withdrawal_status != WithdrawalAutomatic::AUTOMATIC_PAYMENT) {
            return ResponeFails('只有自动出款中才能进行此操作');
        }
        //更改成自动出款失败
        $WithdrawalAutomatic->withdrawal_status=WithdrawalAutomatic::AUTOMATIC_FAILS;
        if(!$WithdrawalAutomatic->save()){
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }

}

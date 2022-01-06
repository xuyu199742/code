<?php
/* 导出excel表格*/
namespace Modules\System\Http\Controllers;

use App\Exports\UserExport;
use Faker\Provider\Payment;
use Models\AdminPlatform\VipBusinessman;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelWithdrawRecord;
use Models\Treasure\RecordDrawScore;
use Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;
use Illuminate\Validation\Rule;
use App\Http\Requests\SelectGameIdRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\GameScoreInfo;

class ExportController extends Controller
{
    /**
     * 导出游戏内金币记录excel数据
     * @return Response
     */
    public function export_getList(SelectGameIdRequest $request)
    {
       /* $list= GameScoreInfo::whereHas("account", function($query) use ($request){
            $query->where('IsAndroid',0)->andFilterWhere('GameID', request('game_id'));
        })->with(['account'=>function($query) use ($request){
            $query->select('UserID','GameID','IsAndroid','NickName')->where('IsAndroid',0)->andFilterWhere('GameID',$request->input('game_id'));
        }])->andFilterBetweenWhere('LastLogonDate',request('start_date'),request('end_date'))
            ->orderBy('Score','desc')
            ->get();*/
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        $list = RecordDrawScore::table()->whereHas('account', function ($query) use ($request){
            $query->from(AccountsInfo::tableName())->andFilterWhere('GameID',$request->input('game_id'));
        })->with(['account'=>function($query) use ($request){
            $query->select('UserID','GameID')->andFilterWhere('GameID',$request->input('game_id'));
        }])->whereHas('darwInfo', function($query) use ($request){
            $query->select('*');
        })->with(['darwInfo'=>function($query) use($request){
            $query->select('*');
        }])->andFilterBetweenWhere('InsertTime',request('start_date'),request('end_date'))
            ->orderBy('InsertTime', 'desc')
            ->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['account']['GameID'],//游戏ID
                $v->InsertTime ? date('Y-m-d H:i:s', strtotime($v->InsertTime)) : '未知',//时间
                $v->darwInfo->kind->KindName ?? '未知',//游戏名称
                $v->darwInfo->server->ServerName ?? '未知',//房间
                $v['DrawID'],//对局标识
                realCoins($v->JettonScore ?? 0),//打码量
                (boolean)$v->IsBanker ? '是':'否',//是否坐庄/叫地主
                realCoins($v->CurScore ?? 0),//当前携带金币
                realCoins($v->Score ?? 0),//输赢
            ];
        }
        $headings = [
            '游戏ID', '时间','游戏','房间','对局标识','打码量','是否坐庄/叫地主','当前携带金币','输赢（'.config('set.rmb').'）'
        ];
        return Excel::download(new UserExport($data,$headings), '游戏内金币记录数据'.date('ymdHis',time()).'.xlsx');
    }
    /**
     * 导出游戏外金币记录excel数据
     * @return Response
     */
    public function export_generalWater(SelectGameIdRequest $request)
    {
        $list = $this->gameIdSearchUserId(request('game_id'), new RecordTreasureSerial())
            ->andFilterWhere('UserID',request('user_id'))
            ->andFilterWhere('TypeID',request('type_id'))
            ->andFilterBetweenWhere('CollectDate',request('start_date'),request('end_date'))
            ->orderBy('CollectDate','desc')
            ->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['account']['GameID'],//游戏ID
                $v['CollectDate'],//时间
                $v['SerialNumber'],//流水号
                $v['TypeText'],//流水类型
                realCoins($v['CurScore']),//操作前携带金币
                realCoins($v['CurInsureScore']),//操作前银行金币
                realCoins($v['ChangeScore']),//金币变化
                $v['ClientIP'],//操作地址
                $v->admin->username??'',//操作人
            ];
        }
        $headings = [
            '游戏ID', '时间','流水号','流水类型','操作前携带金币','操作前银行金币','金币变化','操作地址','操作人'
        ];
        return Excel::download(new UserExport($data,$headings), '游戏外金币记录数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
     * 导出充值订单excel数据
     * @return Response
     * */
    public function export_payments(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(PaymentOrder::STATUS))],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'status.numeric'  => '订单状态必须数字',
            'start_time.date' => '无效日期',
            'end_time.date'   => '无效日期',
        ])->validate();
        $search_time    = $request->input('time_type', 1) == 1 ? 'created_at' : 'success_time';
        $list           = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('payment_status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->orderBy($search_time, 'DESC')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['game_id'],//游戏ID
                $v['order_no'],//订单号
                $v['third_order_no'],//三方订单号
                $v['amount'],//充值
                $v['created_at'],//下单时间
                $v['success_time'],//到账时间
                $v->status_text,//订单状态
                $v['payment_type'],//充值方式
                $v['payment_provider_name'],//充值通道
            ];
        }
        $headings = [
            '游戏ID', '订单号','三方订单号','充值'.config('set.amount').'('.config('set.rmb').')','下单时间','到账时间','订单状态','充值方式','充值通道'
        ];
        //return $headings;
        return Excel::download(new UserExport($data,$headings), '充值订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /**
     * 导出内部充值订单
     * @return Response
     */
    public function export_official(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(PaymentOrder::STATUS))],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'status.numeric'  => '订单状态必须数字',
            'start_time.date' => '无效日期',
            'end_time.date'   => '无效日期',
        ])->validate();
        $search_time       = $request->input('time_type', 1) == 1 ? 'created_at' : 'success_time';
        $list              = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->whereIn('payment_provider_id', array_values(PaymentOrder::OFFICIAL_KEYS))
            ->andFilterWhere('payment_status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->orderBy($search_time, 'DESC')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['game_id'],//游戏ID
                $v['order_no'],//订单号
                $v['third_order_no'],//三方订单号
                $v['amount'],//充值
                $v['created_at'],//下单时间
                $v['success_time'],//到账时间
                $v->status_text,//订单状态
                $v['payment_provider_name'],//充值方式
            ];
        }
        $headings = [
            '游戏ID', '订单号','三方订单号','充值'.config('set.amount').'('.config('set.rmb').')','下单时间','到账时间','订单状态','充值方式'
        ];
        return Excel::download(new UserExport($data,$headings), '内部充值订单数据'.date('ymdHis',time()).'.xlsx');

    }
    /**
     * 导出vip商人订单
     * @return Response
     */
    public function export_vip(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(PaymentOrder::STATUS))],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'status.numeric'  => '订单状态必须数字',
            'start_time.date' => '无效日期',
            'end_time.date'   => '无效日期',
        ])->validate();
        $search_time = $request->input('time_type', 1) == 1 ? 'created_at' : 'success_time';
        $admin = $this->user()->id;
        $list  = VipBusinessman::where('admin_id', $admin)->where('nullity', VipBusinessman::NULLITY_ON)->get();
        $ids  = collect($list)->pluck('id');
        $list = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('payment_status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->whereIn('payment_provider_id', $ids)
            ->where('payment_type', VipBusinessman::SIGN)
            ->orderBy($search_time, 'DESC')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['game_id'],//游戏ID
                $v['order_no'],//订单号
                $v['third_order_no'],//三方订单号
                $v['amount'],//充值
                $v['created_at'],//下单时间
                $v['success_time'],//到账时间
                $v->status_text,//订单状态
                $v['payment_type'],//充值方式
                $v['payment_provider_name'],//充值通道
            ];
        }
        $headings = [
            '游戏ID', '订单号','三方订单号','充值'.config('set.amount').'('.config('set.rmb').')','下单时间','到账时间','订单状态','充值方式','充值通道'
        ];
        return Excel::download(new UserExport($data,$headings), 'vip商人订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
     * 导出订单-订单审核-用户订单
     * */
    public function export_withdrawals(SelectGameIdRequest $request)
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
        $list = WithdrawalOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->orderBy($search_time, 'DESC')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['game_id'],//游戏ID
                $v['order_no'],//订单号
                $v['card_no'],//银行卡号
                $v['bank_info'],//银行信息
                $v['payee'],//收款人
                $v['phone'],//手机号
                $v['money'],
                $v['created_at'],//下单时间
                $v['complete_time'],//到账时间
                $v->status_text,//订单状态
                $v->admin->username ?? '未审核',//审核人
            ];
        }
        $headings = [
            '游戏ID', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '订单审核用户订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
     * 导出订单-订单审核-代理订单
     * */
    public function export_agentWithdraw(Request $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(AgentWithdrawRecord::STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => '代理ID必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_date.date'        => '无效日期',
            'end_date.date'          => '无效日期',
        ])->validate();
        $data = $this->gameIdSearchUserId(request('game_id'), new AgentWithdrawRecord(),'user_id')
            ->andFilterWhere('status', request('status'));
        if (2 == request()->input('time_type',1)){
            $data->where('status',AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'));
        }else{
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'));
        }
        $list = $data->orderBy('id','desc')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['account']['GameID'],//代理标识
                $v['order_no'],//订单号
                $v['back_card'],//银行卡号
                $v['back_name'],//银行信息
                $v['name'],//收款人
                $v['phonenum'],//手机号
                realCoins($v['score']),
                date('Y-m-d H:i:s',strtotime($v['created_at'])) ?? '',//下单时间
                date('Y-m-d H:i:s',strtotime($v['account']['LastLogonDate'])) ?? '',//到账时间
                $v->status_text,//订单状态
                $v->admin->username??'未审核',//审核人
            ];
        }
        $headings = [
            '代理标识', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '订单审核代理订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
    * 导出订单-订单审核-渠道订单
    * */
    public function export_channelWithdraw(Request $request)
    {
        Validator::make(request()->all(), [
            'channel_id' => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(ChannelWithdrawRecord::STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'channel_id.numeric' => 'channel_id必须数字',
            'status.numeric'     => '订单状态必须数字',
            'start_date.date'    => '无效日期',
            'end_date.date'      => '无效日期',
        ])->validate();
        $data = ChannelWithdrawRecord::andFilterWhere('status', request('status'))
            ->andFilterWhere('channel_id', request('channel_id'))
            ->andFilterWhere('status', request('status'));
        if (2 == request()->input('time_type', 1)) {
            $data->where('status', ChannelWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'))
                ->orderby('updated_at', 'desc');
        } else {
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
                ->orderby('created_at', 'desc');
        }
        $list = $data->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['channel_id'],//渠道标识
                $v['order_no'],//订单号
                $v['card_no'],//银行卡号
                $v['bank_info'],//银行信息
                $v['payee'],//收款人
                $v['phone'],//手机号
                $v['value'],
                date('Y-m-d H:i:s',strtotime($v['created_at'])) ?? '',//下单时间
                date('Y-m-d H:i:s',strtotime($v['updated_at'])) ?? '',//到账时间
                $v->status_text,//订单状态
                $v->admin->username??'未审核',//审核人
            ];
        }
        //return $data;
        $headings = [
            '渠道标识', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '订单审核渠道订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
     * 导出订单-财务审核-用户订单
     * */
    public function export_financeList(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(WithdrawalOrder::FINANCE_STATUS))],
            'start_time' => ['nullable', 'date'],
            'end_time'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => 'game_id必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_time.date'        => '无效日期',
            'end_time.date'          => '无效日期',
        ])->validate();
        $search_time      = $request->input('time_type', 1) == 1 ? 'created_at' : 'complete_time';
        $list = WithdrawalOrder::whereIn('status', array_keys(WithdrawalOrder::FINANCE_STATUS))
            ->andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->orderBy($search_time, 'DESC')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['game_id'],//游戏ID
                $v['order_no'],//订单号
                $v['card_no'],//银行卡号
                $v['bank_info'],//银行信息
                $v['payee'],//收款人
                $v['phone'],//手机号
                $v['money'],
                $v['created_at'],//下单时间
                $v['complete_time'],//到账时间
                $v->status_text,//订单状态
                $v->admin->username ?? '未审核',//审核人
            ];
        }
        $headings = [
            '游戏ID', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '财务审核用户订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
     * 导出订单-财务审核-代理订单
     * */
    public function export_agentFinance(Request $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(AgentWithdrawRecord::FINANCE_STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric'        => '代理ID必须数字',
            'status.numeric'         => '订单状态必须数字',
            'start_date.date'        => '无效日期',
            'end_date.date'          => '无效日期',
        ])->validate();
        $data = $this->gameIdSearchUserId(request('game_id'), new AgentWithdrawRecord(),'user_id')
            ->whereIn('status', array_keys(AgentWithdrawRecord::FINANCE_STATUS))
            ->andFilterWhere('status', request('status'));
        if (2 == request()->input('time_type',1)){
            $data->where('status',AgentWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'));
        }else{
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'));
        }
        $list = $data->orderBy('id','desc')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['account']['GameID'],//代理标识
                $v['order_no'],//订单号
                $v['back_card'],//银行卡号
                $v['back_name'],//银行信息
                $v['name'],//收款人
                $v['phonenum'],//手机号
                realCoins($v['score']),
                date('Y-m-d H:i:s',strtotime($v['created_at'])) ?? '',//下单时间
                date('Y-m-d H:i:s',strtotime($v['account']['LastLogonDate'])) ?? '',//到账时间
                $v->status_text,//订单状态
                $v->admin->username??'未审核',//审核人
            ];
        }
        $headings = [
            '代理标识', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '订单审核代理订单数据'.date('ymdHis',time()).'.xlsx');
    }
    /*
    * 导出订单-财务审核-渠道订单
    * */
    public function export_channelFinance(Request $request)
    {
        Validator::make(request()->all(), [
            'channel_id' => ['nullable', 'numeric'],
            'status'     => ['nullable', Rule::in(array_keys(ChannelWithdrawRecord::FINANCE_STATUS))],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'channel_id.numeric' => 'channel_id必须数字',
            'status.numeric'     => '订单状态必须数字',
            'start_date.date'    => '无效日期',
            'end_date.date'      => '无效日期',
        ])->validate();
        $data = ChannelWithdrawRecord::whereIn('status', array_keys(ChannelWithdrawRecord::FINANCE_STATUS))
            ->andFilterWhere('status', request('status'))
            ->andFilterWhere('channel_id', request('channel_id'))
            ->andFilterWhere('status', request('status'));
        if (2 == request()->input('time_type', 1)) {
            $data->where('status', ChannelWithdrawRecord::PAY_SUCCESS)
                ->andFilterBetweenWhere('updated_at', request('start_date'), request('end_date'))
                ->orderby('updated_at', 'desc');
        } else {
            $data->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
                ->orderby('created_at', 'desc');
        }
        $list = $data->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k] = [
                $v['channel_id'],//渠道标识
                $v['order_no'],//订单号
                $v['card_no'],//银行卡号
                $v['bank_info'],//银行信息
                $v['payee'],//收款人
                $v['phone'],//手机号
                $v['value'],
                date('Y-m-d H:i:s',strtotime($v['created_at'])) ?? '',//下单时间
                date('Y-m-d H:i:s',strtotime($v['updated_at'])) ?? '',//到账时间
                $v->status_text,//订单状态
                $v->admin->username??'未审核',//审核人
            ];
        }
        //return $data;
        $headings = [
            '渠道标识', '订单号','银行卡号','银行信息','收款人','手机号',config('set.withdrawal').'数额('.config('set.rmb').')','下单时间','到账时间','订单状态','审核人'
        ];
        return Excel::download(new UserExport($data,$headings), '订单审核渠道订单数据'.date('ymdHis',time()).'.xlsx');
    }

}

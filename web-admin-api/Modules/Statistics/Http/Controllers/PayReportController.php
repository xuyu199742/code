<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelUserRelation;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Transformers\OnlinePaymentReportTransformer;
use Transformers\PaymentRankReportTransformer;
use Transformers\PayUserKeepReportTransformer;
use Transformers\TodayFirstPaymentReportTransformer;
use Transformers\TodayPayRankReportTransformer;
use Transformers\TodayRegisterReportTransformer;

class PayReportController extends Controller
{
   /**
    * 线上支付数据总汇
    *
    */
   public function onlinePayment()
   {
       \Validator::make(request()->all(), [
           'start_date'         => 'nullable|date',
           'end_date'           => 'nullable|date',
       ], [
           'start_date.date'     => '日期格式有误',
           'end_date.date'       => '日期格式有误',
       ])->validate();
       try{
           $start_date = request('start_date');
           $end_date = request('end_date');
           $list = PaymentConfig::with(['orders'=>function($query) use ($start_date,$end_date){
               $query->where('payment_status',PaymentOrder::SUCCESS);
               if ($start_date){
                   $query->where('success_time','>=',$start_date);
               }
               if ($end_date){
                   $query->where('success_time','<=',$end_date.' 23:59:59');
               }
           }])->paginate(config('page.list_rows'));
           foreach ($list as $k => $v){
               $list[$k]['payment_num'] = $v->orders->count();//充值笔数
               $arr = [];
               foreach ($v->orders as $order){
                   if (!in_array($order->user_id,$arr)){
                       $arr[] = $order->user_id;
                   }
               }
               $list[$k]['people_num'] = count($arr);//充值人数
               $list[$k]['payment_money'] = $v->orders->sum('amount');//充值
               unset($list[$k]['orders']);
           }
           return $this->response->paginator($list,new OnlinePaymentReportTransformer());
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

   /**
    * 今日注册信息
    *
    */
   public function todayRegister()
   {
       \Validator::make(request()->all(), [
           'channel_id'         => 'nullable|integer',
           'start_date'         => 'nullable|date',
           'end_date'           => 'nullable|date',
       ], [
           'channel_id.integer'  => '渠道标识有误',
           'start_date.date'     => '日期格式有误',
           'end_date.date'       => '日期格式有误',
       ])->validate();
       try{
           $list = AccountsInfo::from('AccountsInfo as a')
               ->with(['payment'=>function($query){
                   $query->where('payment_status',PaymentOrder::SUCCESS);
               },'withdraw'=>function($query){
                   $query->where('status',WithdrawalOrder::PAY_SUCCESS);
               }])
               ->select('a.UserID','a.GameID','a.RegisterDate','b.channel_id')
               ->leftJoin(ChannelUserRelation::tableName().' as b','a.UserID','=','b.user_id')
               ->where('a.IsAndroid',0)
               ->andFilterBetweenWhere('a.RegisterDate',request('start_date'),request('end_date'))
               ->andFilterWhere('b.channel_id',request('channel_id'))
               ->orderBy('a.RegisterDate','desc')
               ->paginate(config('page.list_rows'));
           foreach ($list as $k => $v){
               $list[$k]['payment_money']   = $v->payment->sum('amount');
               $list[$k]['withdraw_money']  = $v->withdraw->sum('money');
               unset($list[$k]['payment']);
               unset($list[$k]['withdraw']);
           }
           return $this->response->paginator($list,new TodayRegisterReportTransformer());
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

   /**
    * 今日首充信息
    *
    */
   public function todayFirstPayment()
   {
       \Validator::make(request()->all(), [
           'date'           => 'nullable|date',
       ], [
           'date.date'       => '日期格式有误',
       ])->validate();
       try{
           $list = PaymentOrder::from(PaymentOrder::tableName().' as a')
               ->select('a.amount','a.created_at','c.GameID','c.RegisterDate')
               ->rightJoin(\DB::raw('(SELECT MIN (id) AS id FROM '.PaymentOrder::tableName().' WHERE payment_status = '."'".PaymentOrder::SUCCESS."'".' GROUP BY user_id) AS b'),'a.id','=','b.id')
               ->leftJoin(AccountsInfo::tableName().' as c','a.user_id','=','c.UserID');
           $total = clone $list;
           if (request('date')){
               $list = $list ->whereDate('a.created_at',request('date'));
           }
           $list = $list->orderBy('a.created_at','desc')->paginate(config('page.list_rows'));
           //统计总充值
           $sum_money = $total->sum('a.amount');
           return $this->response->paginator($list,new TodayFirstPaymentReportTransformer())->addMeta('sum_money',$sum_money);
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

   /**
    * 支付排行榜
    *
    */
   public function paymentRank()
   {
       \Validator::make(request()->all(), [
           'channel_id'                 => 'nullable|integer',
           'reg_start_date'             => 'nullable|date',
           'reg_end_date'               => 'nullable|date',
           'login_start_date'           => 'nullable|date',
           'login_end_date'             => 'nullable|date',
       ], [
           'channel_id.integer'         => '渠道标识有误',
           'reg_start_date.date'        => '日期格式有误',
           'reg_end_date.date'          => '日期格式有误',
           'login_start_date.date'      => '日期格式有误',
           'login_end_date.date'        => '日期格式有误',
       ])->validate();
       try{
           $list = AccountsInfo::from('AccountsInfo as a')
               ->with(['payment'=>function($query){
                   $query->where('payment_status',PaymentOrder::SUCCESS);
               },'withdraw'=>function($query){
                   $query->where('status',WithdrawalOrder::PAY_SUCCESS);
               },'recordgamesocre'])
               ->select(
                   //活动礼金
                   //\DB::raw("(select sum(ChangeScore) from ".RecordTreasureSerial::tableName()." where UserID=a.UserID and TypeID in(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).")) as active_score"),
                   //拒绝
                   //\DB::raw("(select sum(money) from ".WithdrawalOrder::tableName()." as a1 left join ".WithdrawalAutomatic::tableName()." as b1 on a1.id=b1.order_id where a1.user_id=a.UserID and b1.withdrawal_status=".WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE ." and a1.status='".WithdrawalOrder::PAY_SUCCESS."') as withdrawal_refuse"),
                   'a.UserID','a.GameID','a.RegisterDate','a.RegisterMobile','a.LastLogonDate','b.Score','b.InsureScore','c.channel_id'
                   )
               ->leftJoin(GameScoreInfo::tableName().' as b','a.UserID','=','b.UserID')
               ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
               ->where('a.IsAndroid',0)
               ->andFilterWhere('c.channel_id',request('channel_id'))
               ->andFilterBetweenWhere('a.RegisterDate',request('reg_start_date'),request('reg_end_date'))
               ->andFilterBetweenWhere('a.LastLogonDate',request('login_start_date'),request('login_end_date'))
               ->paginate(config('page.list_rows'));
           foreach ($list as $k => $v){
               $list[$k]['payment_money']   = $v->payment->sum('amount');
               $list[$k]['withdraw_money']  = $v->withdraw->sum('money');
               //盈亏改为：玩家输赢=中奖-投注
               $list[$k]['win_money'] = $v->recordgamesocre->sum('RewardScore')-$v->recordgamesocre->sum('JettonScore');//- intval($v['active_score']) + intval($v['withdrawal_refuse']);
               unset($list[$k]['payment']);
               unset($list[$k]['withdraw']);
               unset($list[$k]['recordgamesocre']);
           }
           return $this->response->paginator($list,new PaymentRankReportTransformer());
       }catch (\Exception $exception){
           return ResponeFails('非法操作');
       }
   }

   /**
    * 日充值排行
    *
    */
   public function todayPayRank()
   {
       \Validator::make(request()->all(), [
           'channel_id'             => 'nullable|integer',
           'date'                   => 'nullable|date',
       ], [
           'channel_id.integer'     => '渠道标识有误',
           'date.date'              => '日期格式有误',
       ])->validate();
       try{
           $date = request('date');
           $whereStr = '';
           if ($date){
               $whereStr = " and created_at < '".date('Y-m-d 23:59:59',strtotime($date))."' and created_at >='".date('Y-m-d',strtotime($date))."'";
           }
           $list = AccountsInfo::from('AccountsInfo as a')
               ->select('a.GameID',
                   \DB::raw("(select sum(amount) from ".PaymentOrder::tableName()." where user_id=a.UserID and payment_status='".PaymentOrder::SUCCESS."'".$whereStr.") as pay_money")
               )
               ->leftJoin(ChannelUserRelation::tableName().' as b','a.UserID','=','b.user_id')
               ->where('a.IsAndroid',0)
               ->andFilterWhere('b.channel_id',request('channel_id'))
               ->orderByRaw("(select sum(amount) from ".PaymentOrder::tableName()." where user_id=a.UserID and payment_status='".PaymentOrder::SUCCESS."'".$whereStr.") DESC")
               ->paginate(config('page.list_rows'));
           return $this->response->paginator($list,new TodayPayRankReportTransformer());
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

   /**
    * 支付用户留存
    *
    */
   public function payUserKeep()
   {
       \Validator::make(request()->all(), [
           'channel_id'             => 'nullable|integer',
       ], [
           'channel_id.integer'     => '渠道标识有误',
       ])->validate();
       try{
           //查询某一天的用户2,3,7,15,30日的留存
           $rightJoinSub = \DB::table(PaymentOrder::tableName().' as a')
               ->select('a.user_id',\DB::raw("format(min(a.created_at),'yyyy-MM-dd') as ctime"))
               ->where('payment_status',PaymentOrder::SUCCESS)
               ->groupBy('a.user_id');
           $list = ChannelUserRelation::from('channel_user_relation as b')
               ->select('t.ctime',\DB::raw("count(*) as num"),\DB::raw("max(channel_id) as channel_id"))
               ->rightJoinSub($rightJoinSub,'t', function($join){
                   $join->on('t.user_id', '=', 'b.user_id');
               })
               ->andFilterWhere('b.channel_id',request('channel_id'))
               ->groupBy('t.ctime')
               ->orderBy('t.ctime','desc')
               ->paginate(10);
           //查询首充用户登录情况
           foreach ($list as $k => $v){
               //获取30天的日期
               $etime_two     = date('Y-m-d', strtotime($v->ctime) + 1 * 86400);
               $etime_three   = date('Y-m-d', strtotime($v->ctime) + 2 * 86400);
               $etime_seven   = date('Y-m-d', strtotime($v->ctime) + 6 * 86400);
               $etime_fifteen = date('Y-m-d', strtotime($v->ctime) + 14 * 86400);
               $etime_thirty  = date('Y-m-d', strtotime($v->ctime) + 29 * 86400);
               //查询该日期的2,3,7,15,30日的留存
               //设置默认值
               $list[$k]['two']     = $this->getLiucun($v->ctime,$etime_two,2);
               $list[$k]['three']   = 0;
               $list[$k]['seven']   = 0;
               $list[$k]['fifteen'] = 0;
               $list[$k]['thirty']  = 0;
               if ($list[$k]['two'] != 0){
                   $list[$k]['three'] = $this->getLiucun($v->ctime,$etime_three,3);
               }
               if ($list[$k]['three'] != 0){
                   $list[$k]['seven'] = $this->getLiucun($v->ctime,$etime_seven,7);
               }
               if ($list[$k]['seven'] != 0){
                   $list[$k]['fifteen'] = $this->getLiucun($v->ctime,$etime_fifteen,15);
               }
               if ($list[$k]['fifteen'] != 0){
                   $list[$k]['thirty'] = $this->getLiucun($v->ctime,$etime_thirty,30);
               }
           }
           return $this->response->paginator($list,new PayUserKeepReportTransformer());
       }catch (\Exception $exception){
           //dd($exception->getMessage());
           return ResponeFails('异常错误');
       }
   }

   /**
    * 留存获取
    * @param string $start_date         开始时间
    * @param string $end_date           结束时间
    * @param int    $num                次数
    *
    */
   private function getLiucun($start_date, $end_date, $num)
   {
       $rightJoinSub = \DB::table(PaymentOrder::tableName().' as a')
           ->select('a.user_id',\DB::raw("format(min(a.created_at),'yyyy-MM-dd') as ctime"))
           ->where('payment_status',PaymentOrder::SUCCESS)
           ->groupBy('a.user_id');
       $list = RecordUserLogon::from('RecordUserLogon as c')
           ->select('c.UserID')
           ->leftJoin(ChannelUserRelation::tableName().' as b','c.UserID','=','b.user_id')
           ->rightJoinSub($rightJoinSub, 't', function($join){
               $join->on('t.user_id', '=', 'c.UserID');
           })
           ->andFilterWhere('b.channel_id',request('channel_id'))
           ->whereBetween('c.CreateDate',[$start_date,$end_date])
           ->where('t.ctime',$start_date)
           ->groupBy('c.UserID')
           ->having(\DB::raw("count(c.UserID)"),'=',$num)
           ->get();
       return count($list);
   }

}

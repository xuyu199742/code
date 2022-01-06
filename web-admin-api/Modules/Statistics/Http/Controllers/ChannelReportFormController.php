<?php
//渠道报表
namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Agent\ChannelWithdrawRecord;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Transformers\ChannelInfoTransformer;
use Validator;

class ChannelReportFormController extends Controller
{
    //渠道报表-渠道列表（计算的是渠道直推用户的）
    public function getInfo()
    {
        Validator::make(request()->all(), [
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'channel_id'=> ['nullable','numeric'],//渠道id
        ], [
            'channel_id.numeric'  => '渠道ID必须是数字'
        ])->validate();
        $start_date = request('start_date');
        $end_date = request('end_date');
        $channel_id = request('channel_id');
        $channel_info = ChannelInfo::where('nullity', ChannelInfo::NULLITY_ON)->andFilterWhere('channel_id', $channel_id)->first();
        if($channel_id){
            if(!$channel_info){
                return ResponeFails('该渠道不存在或已被禁用,请重新输入！');
            }
        }else{
            if(!$channel_info){
                $res=[];
                return $res;
            }
        }
        //盈利，税收，投注量，注册人数，首充人数，注册首充，活动礼金，提款，日活人数
        //新增字段：渠道备注，打码量，首存/人数，二存/人数，三存/人数，总用户，新增用户，充值/人数
        //平台盈利=投注-中奖
        //查询所有的渠道
        $list = ChannelInfo::where('nullity',ChannelInfo::NULLITY_ON)
            ->andFilterWhere('channel_id', request('channel_id'))
            ->orderBy('created_at','desc')
            ->paginate(\request('pagesize',config('page.list_rows')));
        //获取该分页下的channel_id
        $channel_ids = $list->pluck('channel_id');
        list($channel, $people_sum, $pay, $withdrawal, $cash_gift_sum,$login_num,$all_user,$recharge,$second_pay,$third_pay) = $this->statisc_channel($channel_ids,$start_date,$end_date);
        foreach ($list as $k => $v) {
            $list[$k]['start_date']            = $start_date ?? '/';
            $list[$k]['end_date']              = $end_date ?? '/';
            $list[$k]['tax_revenue']           = realCoins($channel[$v->channel_id]->SystemServiceScore ?? 0); //税收
            $list[$k]['bet_sum']               = realCoins($channel[$v->channel_id]->JettonScore ?? 0);//投注量
            $list[$k]['jetton_score']          = realCoins($channel[$v->channel_id]->JettonScore ?? 0);//打码量
            $list[$k]['register_users']        = $people_sum[$v->channel_id]->total ?? 0;//注册人数（新增用户）
            $list[$k]['first_recharge_score']  = $pay[$v->channel_id]->amount ?? 0;//首充
            $list[$k]['first_recharge_users']  = $pay[$v->channel_id]->total ?? 0;//首充人数
            $list[$k]['cash_gift_sum']         = realCoins($cash_gift_sum[$v->channel_id]->ChangeScore ?? 0);//活动礼金
            $list[$k]['withdraw_sum']          = ($withdrawal[$v->channel_id]->money ?? 0);
            $list[$k]['everyday_active']       = ($login_num[$v->channel_id]->total ?? 0);//日活人数
            $list[$k]['profit']                = realCoins($channel[$v->channel_id]->profit ?? 0);//平台盈利=投注总额-中奖总额
            $list[$k]['all_user']              = $all_user[$v->channel_id]->total ?? 0;//总用户
            $list[$k]['recharge_sum']          = $recharge[$v->channel_id]->amount ?? 0;//充值
            $list[$k]['recharge_total']        = $recharge[$v->channel_id]->total ?? 0;//充值人数
            $list[$k]['second_recharge_score'] = $second_pay[$v->channel_id]->amountSum ?? 0;//二充
            $list[$k]['second_recharge_users'] = $second_pay[$v->channel_id]->userSum ?? 0;//二充人数
            $list[$k]['third_recharge_score']  = $third_pay[$v->channel_id]->amountSum ?? 0;//三充
            $list[$k]['third_recharge_users']  = $third_pay[$v->channel_id]->userSum ?? 0;//三充人数
        }
        return $this->response->paginator($list, new ChannelInfoTransformer());
    }

    private function statisc_channel($channel_ids,$start_date,$end_date)
    {
        $timeData = timeTransform([$start_date,$end_date],'day');
        //直推玩家输赢，投注，流水
        $channel = $this->channel_sql($channel_ids, [
            \DB::raw('SUM(b.JettonScore) as JettonScore'),    //投注
            \DB::raw('SUM(b.SystemServiceScore) as SystemServiceScore'), //税收
            \DB::raw('SUM(b.JettonScore-b.RewardScore) as profit')  //平台盈利 = 投注-中奖
        ], [RecordScoreDaily::tableName() . ' as b','a.user_id' , '=', 'b.UserID'],null,['b.UpdateDate',$timeData, null],$start_date);
        //直属推广首次充值人数，首次充值统计
        $qudao_id = $channel_ids ->toArray();
        $qudao_id = join($qudao_id,',');
        $havingWhere = '';
        if (!empty($start_date) && empty($end_date)){
            $havingWhere = " having min(b.created_at) >= '".$start_date."'";
        }
        if (empty($start_date) && !empty($end_date)){
            $havingWhere = " min(b.created_at) <= '".$end_date." 23:59:59'";
        }
        if (!empty($start_date) && !empty($end_date)){
            $havingWhere = " having min(b.created_at) >= '".$start_date."' and min(b.created_at) <= '".$end_date." 23:59:59'";
        }
        $res = \DB::select("SELECT e.channel_id,COUNT(*) AS total,SUM(e.amount) AS amount FROM (
                SELECT c.*,d.amount FROM (SELECT MIN(b.id) AS id,b.user_id,MIN(b.created_at) AS created_at,a.channel_id 
                FROM  AgentDB.dbo.channel_user_relation AS a 
                LEFT JOIN  admin_platform.dbo.payment_orders AS b ON a.user_id = b.user_id 
                WHERE b.payment_status = 'SUCCESS' AND [a].[channel_id] IN (".$qudao_id.") 
                GROUP BY b.user_id,a.channel_id ".$havingWhere.") AS c 
                LEFT JOIN admin_platform.dbo.payment_orders AS d ON c.id=d.id) AS e GROUP BY e.channel_id");
        $pay = [];
        array_map(function ($v) use(&$pay){
            $pay[$v->channel_id]  = $v;
        },$res);
        //直属推广统计
        $withdrawal = $this->channel_sql($channel_ids, [
            \DB::raw('SUM(b.money) as money'),
            \DB::raw('COUNT(DISTINCT b.user_id) as total')
        ], [WithdrawalOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'], ['b.status', WithdrawalOrder::PAY_SUCCESS, null],['b.complete_time',$timeData,null],$start_date);
        //直属推广玩家人数（新增用户）
        /*$people_sum = $this->channel_sql($channel_ids, [
            \DB::raw('COUNT(DISTINCT user_id) as total')
        ], [AccountsInfo::tableName() . ' as b', 'a.user_id', '=', 'b.UserID'],null,['b.RegisterDate',$timeData,null],$start_date);*/
        $people_sum = ChannelUserRelation::select('channel_id',
            \DB::raw('COUNT(user_id) as total')
        )
            ->whereIn('channel_id', $channel_ids)
            ->andFilterBetweenWhere('created_at',$start_date,$end_date)
            ->groupBy('channel_id')
            ->get()->keyBy('channel_id');
        //活动礼金
        $cash_gift_sum = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(RecordTreasureSerial::tableName().' as b','a.user_id','=','b.UserID')
            ->select('a.channel_id',
                \DB::raw('SUM(b.ChangeScore) as ChangeScore')
            )
            ->where('b.ChangeScore', '>', 0)
            ->whereIn('b.TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
            ->andFilterBetweenWhere('b.CollectDate',$start_date,$end_date)
            ->whereIn('a.channel_id', $channel_ids)
            ->groupBy('a.channel_id')
            ->get()->keyBy('channel_id');
        //拒绝总额
        /*$withdraw_refuse = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(WithdrawalOrder::tableName().' as b','a.user_id','=','b.user_id')
            ->leftJoin(WithdrawalAutomatic::tableName().' as c','b.id','=','c.order_id')
            ->select('a.channel_id',
                \DB::raw('SUM(b.money) as Score')
            )
            ->where('b.status', WithdrawalOrder::PAY_FAILS) //汇款失败
            ->where('c.withdrawal_status', WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE) //人工出款失败，拒绝
            ->whereNull('b.withdrawal_type')
            ->andFilterBetweenWhere('b.created_at',$start_date,$end_date)
            ->whereIn('a.channel_id', $channel_ids)
            ->groupBy('a.channel_id')
            ->get()->keyBy('channel_id');*/
        //直属推广总用户
        $all_user= ChannelUserRelation::select('channel_id',
            \DB::raw('COUNT(user_id) as total')
        )
            ->whereIn('channel_id', $channel_ids)
            ->groupBy('channel_id')
            ->get()->keyBy('channel_id');
        //充值/人数
        $recharge= $this->channel_sql($channel_ids, [
            \DB::raw('SUM(b.amount) as amount'),
            \DB::raw('COUNT(DISTINCT b.user_id) as total')
        ], [PaymentOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'],
            ['b.payment_status', PaymentOrder::SUCCESS, null],['b.success_time',$timeData,null],$start_date);
        $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
        $end_time = date('Y-m-d 23:59:59',strtotime($end_date));
        //二存/人数
        $second_recharge=\DB::select("SELECT SUM (a.amount) AS amountSum, COUNT (b.user_id) AS userSum, b.channel_id 
                FROM AgentDB.dbo.channel_user_relation AS b LEFT JOIN admin_platform.dbo.payment_orders a ON b.user_id = a.user_id
                WHERE a.id = (SELECT TOP 1 id FROM admin_platform.dbo.payment_orders WHERE user_id = a.user_id AND payment_status = 'SUCCESS'
                AND id NOT IN (SELECT TOP 1 id 
                FROM admin_platform.dbo.payment_orders WHERE user_id = a.user_id AND payment_status = 'SUCCESS'
                ORDER BY created_at)
                ORDER BY created_at) AND a.created_at between '".$start_time."' and '".$end_time."' and b.channel_id IN (".$qudao_id.") GROUP BY b.channel_id");
        $second_pay = [];
        array_map(function ($v) use(&$second_pay){
            $second_pay[$v->channel_id]  = $v;
        },$second_recharge);
        //三存/人数
        $third_recharge=\DB::select("SELECT SUM (a.amount) AS amountSum,COUNT (b.user_id) AS userSum,b.channel_id
               FROM	AgentDB.dbo.channel_user_relation AS b LEFT JOIN admin_platform.dbo.payment_orders a ON b.user_id = a.user_id
               WHERE a.id = (SELECT	TOP 1 id FROM admin_platform.dbo.payment_orders	WHERE user_id = a.user_id AND payment_status = 'SUCCESS'
               AND id NOT IN (SELECT TOP 2 id
			   FROM	admin_platform.dbo.payment_orders WHERE	user_id = a.user_id	AND payment_status = 'SUCCESS'
			   ORDER BY	created_at)
		       ORDER BY	created_at) AND a.created_at between '".$start_time."' and '".$end_time."' and b.channel_id IN (".$qudao_id.") GROUP BY b.channel_id");
        $third_pay = [];
        array_map(function ($v) use(&$third_pay){
            $third_pay[$v->channel_id]  = $v;
        },$third_recharge);
        //日活跃人数
        if($start_date && $end_date){
            $login_data = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
                ->leftJoin(RecordUserLogon::tableName().' as b','a.user_id','=','b.UserID')
                ->select('a.channel_id','b.CreateDate',
                    \DB::raw('COUNT(DISTINCT b.UserID) as total')
                )
                ->andFilterBetweenWhere('b.CreateDate',$start_date,$end_date)
                ->whereIn('a.channel_id', $channel_ids)
                ->groupBy('a.channel_id','b.CreateDate')
                ->get();
            $login_num = [];
            foreach ($login_data as $k => $v){
                //分组
                if (!isset($login_num[$v['channel_id']])){
                    $login_num[$v['channel_id']] = $v;
                }
                if ($login_num[$v['channel_id']]['total'] < $v['total']){
                    $login_num[$v['channel_id']] = $v;
                }
            }
        }else{
            $start_date = date('Y-m-d');
            $login_num = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
                ->leftJoin(RecordUserLogon::tableName().' as b','a.user_id','=','b.UserID')
                ->select('a.channel_id',
                    \DB::raw('COUNT(DISTINCT b.UserID) as total')
                )
                ->andFilterBetweenWhere('b.CreateDate',$start_date,$start_date)
                ->whereIn('a.channel_id', $channel_ids)
                ->groupBy('a.channel_id')
                ->get()->keyBy('channel_id');
        }
        return [$channel, $people_sum, $pay, $withdrawal, $cash_gift_sum,$login_num,$all_user,$recharge,$second_pay,$third_pay];
    }

    private function channel_sql($channel_ids, $select, $join = null, $where = null,$where_between,$start_date)
    {
        $select = array_merge($select, ['a.channel_id']);
        $result = \DB::table(ChannelUserRelation::tableName() . ' AS a')->select($select);
        if ($join) {
            list($table, $first, $operator, $second) = $join;
            $result = $result->leftJoin($table, $first, $operator, $second);
        }
        if ($where) {
            list($column, $value, $operators) = $where;
            $result = $result->where($column, $operators ?? '=', $value);
        }
        if ($where_between && $start_date) {
            list($column, $value,) = $where_between;
            $result = $result->whereBetween($column, $value);
        }
        $result = $result->whereIn('a.channel_id', $channel_ids)
            ->groupBy('a.channel_id')
            ->get()->keyBy('channel_id');
        return $result;
    }
}

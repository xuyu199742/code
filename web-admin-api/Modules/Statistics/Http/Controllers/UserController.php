<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\Accounts\MembersInfo;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\AccountsSet;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalAutomatic;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentWithdrawRecord;
use Models\Agent\ChannelUserRelation;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Transformers\UserReportTransformer;

class UserController extends Controller
{
    /**
     * 用户报表
     *
     */
    public function userReport()
    {
        try{
            /*$date_type  = request('date_type',1);
            $start_date = request('start_date','');
            $end_date   = request('end_date','');*/
            //去掉派彩，盈利字段
            //新增日期，游戏和房间号筛选流水，投注，中奖，输赢字段
            $start_time = request('start_time',date('Y-m-d'));
            $end_time   = request('end_time',date('Y-m-d'));
            $kind_id    = request('kind_id','');
            $server_id  = request('server_id','');
            $member_order = request('member_order');
            if($member_order){
                $exists = MembersInfo::query()->where('MemberOrder',$member_order)->exists();
                if(!$exists){
                    return ResponeFails('该会员等级不存在');
                }
            }
            $list = AccountsInfo::from('AccountsInfo as a')
                ->with([
                    'agent','channel','userLogin'
                ])
                ->select('a.UserID','a.GameID','a.RegisterDate','a.MemberOrder as vip_level','b.Score','b.InsureScore','b.JettonScore','c.channel_id','d.coins',
                    //充值
                    \DB::raw("(select sum(amount) from ".PaymentOrder::tableName()." where user_id=a.UserID and payment_status='".PaymentOrder::SUCCESS."') as pay_score"),
                    //充值次数
                    \DB::raw("(select count(*) from ".PaymentOrder::tableName()." where user_id=a.UserID and payment_status='".PaymentOrder::SUCCESS."') as pay_num"),

                    \DB::raw("(select sum(money) from ".WithdrawalOrder::tableName()." where user_id=a.UserID and status='".WithdrawalOrder::PAY_SUCCESS."') as withdrawal_score"),
                    //拒绝
                    //\DB::raw("(select sum(money) from ".WithdrawalOrder::tableName()." as a1 left join ".WithdrawalAutomatic::tableName()." as b1 on a1.id=b1.order_id where a1.user_id=a.UserID and b1.withdrawal_status=".WithdrawalAutomatic::ARTIFICIAL_FAILS_REFUSE ." and a1.status='".WithdrawalOrder::PAY_SUCCESS."') as withdrawal_refuse"),
                    //次数
                    \DB::raw("(select count(*) from ".WithdrawalOrder::tableName()." where user_id=a.UserID and status='".WithdrawalOrder::PAY_SUCCESS."') as withdrawal_num"),
                    //输赢修改为：玩家输赢=中奖-投注-->改为：玩家输赢字段ChangeScore
                    \DB::raw("(select sum(ChangeScore) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as winlose"),
                    //流水
                    \DB::raw("(select sum(StreamScore) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as water"),
                    //有效投注
                    \DB::raw("(select sum(JettonScore) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as jetton_score"),
                    //投注
                    \DB::raw("(select sum(ValidJettonScore) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as bet"),
                    //中奖-->改为：有效投注 + 玩家输赢
                    \DB::raw("(select sum(JettonScore+ChangeScore) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as WinnerPaid"),
                    //投注次数
                    \DB::raw("(select count(*) from ".RecordGameScore::tableName()." where UserID=a.UserID ".$this->searchkind($kind_id,$server_id)." and UpdateTime between '".$start_time."' and '".$end_time." 23:59:59') as bet_num"),
                    //活动礼金
                    \DB::raw("(select sum(ChangeScore) from ".RecordTreasureSerial::tableName()." where UserID=a.UserID and TypeID in(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).")) as active_score"),
                    //活动次数
                    \DB::raw("(select count(*) from ".RecordTreasureSerial::tableName()." where UserID=a.UserID  and TypeID IN(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).")) as active_num")
                )
                ->leftJoin(GameScoreInfo::tableName().' as b','a.UserID','=','b.UserID')
                ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
                ->leftJoin(FirstRechargeLogs::tableName().' as d','a.UserID','=','d.user_id')
                ->leftJoin(AccountsSet::tableName().' as e','a.UserID','=','e.user_id')
                ->where('a.IsAndroid',0)
                ->andFilterWhere('a.MemberOrder',request('member_order'))
                ->andFilterWhere('a.GameID',request('game_id'));
               // ->andFilterWhere('c.channel_id',request('channel_id'));
           /* if($date_type == 1){
                $list->andFilterBetweenWhere('a.RegisterDate',$start_date,$end_date);
            }elseif ($date_type == 2){
                $list->whereExists(function ($query) {
                    $query->select(\DB::raw(1))->from(RecordUserLogon::tableName().' as f')->whereRaw('f.UserID=a.UserID');
                    $s = request('start_date','');
                    $e = request('end_date','');
                    if($s){ $query->where('CreateDate','>=',$s); }
                    if($e){ $query->where('CreateDate','<=',$e); }
                });
            }
            $list = $this->searchStatus($list, request('status_type'));*/
            $list = $list->orderBy('a.UserID','desc')->paginate(10);
            foreach ($list as $k => $v){
                //游戏天数
                $list[$k]['game_days']          = $v->userLogin->count();
                //上级代理
                $list[$k]['parent_game_id']     = $this->getGameId($v->agent->parent_user_id ?? '');
                //中奖
               /* $list[$k]['WinnerPaid']         = intval($v->recordgamesocre->sum('RewardScore'));
                //有效投注
                $list[$k]['ValidJettonScore']   = intval($v->recordgamesocre->sum('JettonScore'));
                //派彩 = 投注 - 中奖
                $list[$k]['PayoutScore']        = $list[$k]['JettonScore'] - $list[$k]['WinnerPaid'];
                //盈利 = 投注 - 中奖总额 - 活动礼金总额（优惠、返利）+ 拒绝总额
                $list[$k]['profit'] = $list[$k]['JettonScore'] - $list[$k]['WinnerPaid'] - intval($v['active_score']) + intval($v['withdrawal_refuse']);
                unset($list[$k]['recordgamesocre']);*/
                unset($list[$k]['agent']);
                unset($list[$k]['channel']);
                unset($list[$k]['userLogin']);
            }
            $vips = UserLevel::orderBy('LevelID','asc')->pluck('LevelName','LevelID');
            return $this->response->paginator($list,new UserReportTransformer())->addMeta('vip_lists',$vips);
        }catch (\Exception $exception){
            //dd($exception->getMessage());
            return ResponeFails('非法操作');
        }
    }

    /*
     * VIP人数统计
     */
    public function vipStatistics(){
        /*$data = AccountsInfo::query()
            ->selectRaw("MemberOrder,count(*) as vip_count")
            ->where('IsAndroid',0)
            ->groupBy("MemberOrder")
            ->get();
        $total = $data->sum('vip_count');
        $list = $data->map(function($item)use($total){
            $item->rate = bcdiv($item->vip_count * 100 , $total,2).'%' ;
            return $item;
        });*/
        $vip_lists = UserLevel::orderBy('LevelID')->pluck('LevelID');
        $total =0;
        foreach ($vip_lists as $k=>$v){
            $data = AccountsInfo::query()
                ->selectRaw("count(*) as vip_count")
                ->where('IsAndroid',0)
                ->where('MemberOrder',$v)
                ->first();
            $list[$k]['MemberOrder'] = $v;
            $list[$k]['vip_count']   = $data['vip_count'];
            $total += $data['vip_count'];
        }
        foreach($list as $k=>$v){
            $list[$k]['rate']   = bcdiv($v['vip_count'] * 100 , $total,2).'%' ;
        }
        return ResponeSuccess('请求成功',['list' => $list , 'total' => $total]);
    }

    /**
     * 用户报表筛选条件方法
     *
     */
    private function searchkind($kind_id,$server_id)
    {
        $whereStr = '';
        if (!empty($kind_id) && ($kind_id != 'undefined')){
            $whereStr  .= " and KindID=" . $kind_id;
        }
        if (!empty($server_id) && ($server_id != 'undefined')){
             $whereStr  .= " and ServerID=" . $server_id;
        }
        return $whereStr;
    }

    /**
     * 登录状态筛选
     *
     */
    protected function searchStatus(&$obj, $status_type)
    {
        switch ($status_type) {
            //全部启用
            case 1:
                $obj->where(function ($query) {
                    $query->orWhere(function ($query) {
                        $query->where('e.nullity', AccountsSet::NULLITY_ON)->where('e.withdraw', AccountsSet::WITHDRAW_ON);
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNull('e.nullity')->whereNull('e.withdraw');
                    });
                });
                break;
            //禁止登录
            case 2:
                $obj->where(function ($query) {
                    $query->where('e.nullity', AccountsSet::NULLITY_OFF);
                });
                break;

            case 3:
                $obj->where(function ($query) {
                    $query->where('e.withdraw', AccountsSet::WITHDRAW_OFF);
                });
                break;
            //全部禁止
            case 4:
                $obj->where(function ($query) {
                    $query->orWhere(function ($query) {
                        $query->where('e.nullity', AccountsSet::NULLITY_OFF)->where('e.withdraw', AccountsSet::WITHDRAW_OFF);
                    });
                });
                break;
        }
        return $obj;
    }
}

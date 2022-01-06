<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\StatisticsOnlineData;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\Platform\GameRoomInfo;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Transformers\ChannelPayReportTransformer;
use Transformers\DaysActiveReportTransformer;
use Transformers\GameReportTransformer;
use Transformers\RealChannelReportTransformer;
use Validator,DB;

class GameReportFormController extends Controller
{
    //游戏报表
    public function getInfo()
    {
        Validator::make(request()->all(), [
            'kindId'     => ['nullable', 'numeric'],
            'serverId'     => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ])->validate();
        $start_date = date('Y-m-d 00:00:01',strtotime(request('start_date',date('Y-m-d'))));
        $end_date = date('Y-m-d 23:23:59',strtotime(request('end_date',date('Y-m-d'))));
        $list = GameRoomInfo::from('GameRoomInfo AS c')
            ->select('c.ServerID','c.ServerName','c.KindID',
                \DB::raw("(select max(t.online_num) from (SELECT COUNT(DISTINCT a.UserID) as online_num FROM WHQJTreasureDB.dbo.RecordUserInout a
                LEFT JOIN WHQJAccountsDB.dbo.AccountsInfo b ON a.UserID = b.UserID
                WHERE b.IsAndroid = 0 AND c.ServerLevel != 1 AND a.ServerID = c.ServerID AND
                ((a.EnterTime BETWEEN '".$start_date."' AND '".$end_date."')
                OR (a.EnterTime <= '".$start_date."' AND a.LeaveTime >= '".$end_date."')
                OR (a.LeaveTime BETWEEN '".$start_date."' AND '".$end_date."'))
                GROUP BY a.ServerID,CONVERT (VARCHAR (10),a.EnterTime,23)) AS t) as OnlineCount")
            )
            ->andFilterWhere('c.KindID',request('kindId'))
            ->andFilterWhere('c.ServerID',request('serverId'))
            ->orderBy('c.ServerID')
            ->paginate(20);
        $gameScore = RecordGameScore::select(
                'ServerID',
                \DB::raw('COUNT (DISTINCT UserID) AS JettonCount'),
                \DB::raw('SUM (JettonScore) AS JettonTotal'),
                \DB::raw('SUM (JettonScore-RewardScore) AS SystemTotal'), //系统输赢改为：平台盈利=投注-中奖
                \DB::raw('SUM (SystemServiceScore) AS ServiceTotal')
            )
            ->andFilterBetweenWhere('UpdateTime',$start_date,$end_date)
            ->andFilterWhere('KindID',request('kindId'))
            ->andFilterWhere('ServerID',request('serverId'))
            ->groupBy('ServerID')
            ->get()->toArray();
        $scoreList = array_column($gameScore, null, 'ServerID');
        foreach ($list as $k => $item){
            $list[$k]['JettonCount'] = $scoreList[$item->ServerID]['JettonCount'] ?? 0;
            $list[$k]['JettonTotal'] = $scoreList[$item->ServerID]['JettonTotal'] ?? 0;
            $list[$k]['SystemTotal'] = $scoreList[$item->ServerID]['SystemTotal'] ?? 0;
            $list[$k]['ServiceTotal'] = $scoreList[$item->ServerID]['ServiceTotal'] ?? 0;
        }
        return $this->response->paginator($list, new GameReportTransformer());
    }


    /**
     * 获取实时推广数据
     */
    public function getRealList(){
        Validator::make(request()->all(), [
            'channel_id'  => ['nullable', 'numeric'],
            'game_id'     => ['nullable', 'numeric'],
            'regist_start'=> ['nullable', 'date'],
            'regist_end'  => ['nullable', 'date'],
            'login_start' => ['nullable', 'date'],
            'login_end'   => ['nullable', 'date']
        ])->validate();
        $list = ChannelUserRelation::from('channel_user_relation as b')->select(
            'b.channel_id','c.GameID','c.RegisterDate','c.LastLogonDate','c.RegisterIP',
            DB::raw('(select sum(amount) from '.PaymentOrder::tableName().' WHERE b.user_id = user_id and payment_status = '."'".PaymentOrder::SUCCESS."'".') AS RechargeSum'),
            DB::raw('(select sum(money) from '.WithdrawalOrder::tableName().' WHERE b.user_id = user_id and status = '."'".WithdrawalOrder::PAY_SUCCESS."'".') AS WithdrawSum'),
            DB::raw('(select sum(JettonScore) from '.GameScoreInfo::tableName().' where b.user_id = UserID) as JettonSum')
        )
        ->leftJoin(AccountsInfo::tableName().' as c','b.user_id','=','c.UserID')
        ->andFilterBetweenWhere('c.RegisterDate',request('regist_start'),request('regist_end'))
        ->andFilterBetweenWhere('c.LastLogonDate',request('login_start'),request('login_end'))
        ->andFilterWhere('b.channel_id',request('channel_id'))
        ->andFilterWhere('c.GameID',request('game_id'))
        ->groupBy('b.channel_id','c.GameID','c.RegisterDate','c.LastLogonDate','c.RegisterIP','b.user_id')
        ->paginate(20);
        $paymentOrder = ChannelUserRelation::from('channel_user_relation as b')->select(
            DB::raw('sum(d.amount) as RechargeSum'),
            DB::raw('count(DISTINCT d.user_id) as RechargeNum')
        )
        ->leftJoin(DB::raw('(SELECT user_id,payment_status,amount FROM '.PaymentOrder::tableName().' WHERE payment_status = '."'".PaymentOrder::SUCCESS."'".') AS d'),'b.user_id','=','d.user_id')
        ->first()->toArray();
        $withdrawalOrder = ChannelUserRelation::from('channel_user_relation as b')->select(
            DB::raw('sum(e.money) as WithdrawSum'),
            DB::raw('count(DISTINCT e.user_id) as WithdrawNum')
        )
        ->leftJoin(DB::raw('(SELECT user_id,status,money FROM '.WithdrawalOrder::tableName().' WHERE status = '."'".WithdrawalOrder::PAY_SUCCESS."'".') AS e '),'b.user_id','=','e.user_id')
        ->first()->toArray();
        $paymentOrder['RechargeSum']    = $paymentOrder['RechargeSum'] ?? '0.00';
        $withdrawalOrder['WithdrawSum'] = $withdrawalOrder['WithdrawSum'] ?? '0.00';
        return $this->response->paginator($list, new RealChannelReportTransformer())->addMeta('total',[array_merge($paymentOrder,$withdrawalOrder)]);
    }

    /**
     * 推广用户付费
     */
    public function getChannelPay(){
        Validator::make(request()->all(), [
            'channel_id'  => ['nullable', 'numeric'],
        ])->validate();
        if(request('channel_id')) {
            $is_exit = ChannelInfo::where('channel_id', request('channel_id'))->first();
            if (!$is_exit) {
                return ResponeFails('该渠道不存在，请重新输入');
            }
        }
        $list = AccountsInfo::from('AccountsInfo as a')->select(
            DB::raw('MAX(b.channel_id) as channel_id'),
            DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23) as DateTime'),
            DB::raw('count(a.UserID) as AccountsNum')
        )
        ->leftJoin(ChannelUserRelation::tableName().' as b','a.UserID','=','b.user_id')
        ->andFilterWhere('b.channel_id',request('channel_id'))
        ->where('a.IsAndroid',0)
        ->groupBy(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'))
        ->orderBy(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'))
        ->paginate(20);
       // return $list;
        foreach ($list as $k => $item){
            $DateTime = $list[$k]['DateTime'];
            $list[$k]['LTV1'] = 0;
            $list[$k]['LTV2'] = 0;
            $list[$k]['LTV3'] = 0;
            $list[$k]['LTV4'] = 0;
            $list[$k]['LTV7'] = 0;
            $list[$k]['LTV30'] = 0;
            $list[$k]['LTV60'] = 0;
            $AccountsNum = $list[$k]['AccountsNum'] ?: 0;
            //判断该日有没有注册用户
            if($AccountsNum){
                $strTime = strtotime($DateTime);
                $timeVal = [
                    'LTV1' => date('Y-m-d',strtotime('+1 day',$strTime)),
                    'LTV2' => date('Y-m-d',strtotime('+2 day',$strTime)),
                    'LTV3' => date('Y-m-d',strtotime('+3 day',$strTime)),
                    'LTV4' => date('Y-m-d',strtotime('+4 day',$strTime)),
                    'LTV7' => date('Y-m-d',strtotime('+7 day',$strTime)),
                    'LTV30' => date('Y-m-d',strtotime('+30 day',$strTime)),
                    'LTV60' => date('Y-m-d',strtotime('+60 day',$strTime))
                ];

                foreach ($timeVal as $time => $ti){
                    $list[$k][$time] = AccountsInfo::from('AccountsInfo as a')->select(
                        DB::raw('(SUM (b.amount) / '.$AccountsNum.') as amounts')
                    )
                    ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
                    ->leftJoin(ChannelUserRelation::tableName().' as c','a.UserID','=','c.user_id')
                    ->where(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'),$DateTime)
                    ->where('b.payment_status','SUCCESS')
                    ->where('a.IsAndroid',0)
                    ->whereBetween(DB::raw('CONVERT (VARCHAR (10),b.success_time,23)'),[$DateTime,$ti])
                    ->andFilterWhere('c.channel_id',request('channel_id'))
                    ->value('amounts');
                }
            }
        }
        return $this->response->paginator($list, new ChannelPayReportTransformer());
    }

    /**
     * 日活跃状态
     */
    public function getDaysActive(){

        Validator::make(request()->all(), [
            'start_time'  => ['nullable', 'date'],
        ])->validate();
        $timeInit = getInitTime(request('start_time',date('Y-m-d')),'month');
        $list = AccountsInfo::from('AccountsInfo as a')->select(
            DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23) as DateTime'),
            DB::raw('count(a.UserID) as RegisterNum'),
            DB::raw('(SELECT count(*) FROM '.RecordUserLogon::tableName().' WHERE CONVERT (VARCHAR (10),a.RegisterDate,23) = CreateDate) as LoginNum'),
            DB::raw('(SELECT max(total) FROM '.StatisticsOnlineData::tableName().' WHERE CONVERT (VARCHAR (10),a.RegisterDate,23) = statistics_time AND client_type = -1) as HighestNum')
        )
        ->where('a.IsAndroid',0)
        ->whereBetween('a.RegisterDate',$timeInit)
        ->groupBy(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'))
        ->orderBy(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'))
        ->get()->toArray();
        foreach ($list as $k => $item){
            $DateTime = $list[$k]['DateTime'];
            $list[$k]['two'] = 0;
            $list[$k]['three'] = 0;
            $list[$k]['seven'] = 0;
            $list[$k]['fifteen'] = 0;
            $list[$k]['thirty'] = 0;
            $strTime = strtotime($DateTime);
            $timeVal = [
                'two' => date('Y-m-d',strtotime('+1 day',$strTime)),
                'three' => date('Y-m-d',strtotime('+2 day',$strTime)),
                'seven' => date('Y-m-d',strtotime('+6 day',$strTime)),
                'fifteen' => date('Y-m-d',strtotime('+14 day',$strTime)),
                'thirty' => date('Y-m-d',strtotime('+29 day',$strTime)),
            ];
            $timeNum = [
                'two' => 2,
                'three' => 3,
                'seven' => 7,
                'fifteen' => 15,
                'thirty' => 30,
            ];
            foreach ($timeVal as $time => $ti){
                $ac = AccountsInfo::from('AccountsInfo as a')->select('a.UserID')
                ->leftJoin(RecordUserLogon::tableName().' as b','a.UserID','=','b.UserID')
                ->where('a.IsAndroid',0)
                ->where(DB::raw('CONVERT (VARCHAR (10),a.RegisterDate,23)'),$DateTime)
                ->whereBetween(DB::raw('CONVERT (VARCHAR (10),b.CreateDate,23)'),[$DateTime,$ti])
                ->groupBy('a.UserID')
                ->having(DB::raw("count(b.CreateDate)"),'=',$timeNum[$time])
                ->get();
                $list[$k][$time] = count($ac);
                if(!$list[$k][$time]){
                    break;
                }
            }
        }
        $getDateRange = getDateRange($timeInit[0],$timeInit[1]);
        $data = [];
        $dateKeys = array_column($list,'DateTime');
        foreach ($getDateRange as $dk => $date){
            $dateKey = array_search($date,$dateKeys);
            if($dateKey !== false){
                $list[$dateKey]['two'] = $list[$dateKey]['two'] ? bcdiv( $list[$dateKey]['two'] * 100,$list[$dateKey]['RegisterNum'],2) : 0;
                $list[$dateKey]['three'] = $list[$dateKey]['three'] ? bcdiv( $list[$dateKey]['three'] * 100,$list[$dateKey]['RegisterNum'],2) : 0;
                $list[$dateKey]['seven'] = $list[$dateKey]['seven'] ? bcdiv( $list[$dateKey]['seven'] * 100,$list[$dateKey]['RegisterNum'],2) : 0;
                $list[$dateKey]['fifteen'] = $list[$dateKey]['fifteen'] ? bcdiv( $list[$dateKey]['fifteen'] * 100,$list[$dateKey]['RegisterNum'],2) : 0;
                $list[$dateKey]['thirty'] = $list[$dateKey]['thirty'] ? bcdiv( $list[$dateKey]['thirty'] * 100,$list[$dateKey]['RegisterNum'],2) : 0;
                $data[] = $list[$dateKey];
            }else{
                $data[] = [
                    'DateTime' => $date,
                    'RegisterNum' => 0,
                    'LoginNum' => RecordUserLogon::whereDate('CreateDate',$date)->count() ?: 0,
                    'HighestNum' => StatisticsOnlineData::whereDate('statistics_time',$date)->where('client_type','-1')->max('total') ?: 0,
                    'two' => 0,
                    'three' => 0,
                    'seven' => 0,
                    'fifteen' => 0,
                    'thirty' => 0
                ];
            }
        }
        $data = array_reverse($data);
        return ResponeSuccess('请求成功', $data);

    }

}


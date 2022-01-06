<?php
/*金币流水记录表*/

namespace Models\Record;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Models\Accounts\AccountsInfo;
use Models\Accounts\SystemStatusInfo;
use Models\AdminPlatform\AdminUser;
use Models\AdminPlatform\Dict;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;

/**
 * SerialNumber：    流水号
 * MasterID：        后台操作管理员
 * UserID：          流水用户标识
 * TypeID：          操作类型
 * CurScore：        操作前携带金币
 * CurInsureScore：  操作前保险箱金币
 * ChangeScore：     操作变化金币
 * ClientIP：        操作地址
 * CollectDate：     操作时间
 */
class RecordTreasureSerial extends Base
{
	protected $table = 'RecordTreasureSerial';
	const SYSTEM_GIVE_TYPE = 0;
	const REGISTER_GIVE_TYPE = 1;
	const TRANSFER_TYPE = 2;
	const COLLECT_TYPE = 3;
	//const BUY_PROP_TYPE                = 4;
	//const DIAMOND_EXCHANGE_TYPE        = 5;
	const BANK_DEPOSIT_TYPE = 6;
	const BANK_WITHDRAWAL_TYPE = 7;
	const BANK_WITHDRAWAL_REVENUE_TYPE = 8;
	const BACKPACK_TYPE = 10;
	const WITHDRAWAL_TYPE = 11;
	const PAY_TYPE = 12;
	const INNER_PAY_TYPE = 13;
	const SIGN_IN_TYPE = 14;
	const WITHDRAWAL_BACK = 15;
	const BIND_GIVE = 16;
	const ACTIVITY_DISH = 17;
	const VIP_BUSINESS = 18;
	const RED_PACKET = 19;
	const CHANNEL_PAY = 20;
	const VIP_WITHDRAWAL = 21;
	const VIP_WITHDRAWAL_BACK = 22;
	const FIRST_PAY_TYPE = 23;
	const AGENT_RED_PACKET = 24;
	const SYSTEM_GIVE_UP = 25;
	const SYSTEM_GIVE_DOWN = 26;
	const PLAYERS_VIP_CASH = 27;
    const STREAM_SCORE_REBATE = 28;
    const BET_RETURN_REBATE = 29;
    const PROFIT_RETURN_REBATE = 30;
    const LOSS_RETURN_REBATE = 31;
    const TASK_ACTIVITY = 32;
    const TASK_ACTIVITY_ADDITION = 33; //VIP任务加成
    const STREAM_SCORE_ADDITION = 34;  //VIP流水加成
    const BET_RETURN_ADDITION = 35;    //VIP下注加成
    const PROFIT_RETURN_ADDITION = 36; //VIP盈利加成
    const LOSS_RETURN_ADDITION = 37;   //VIP回血加成
    const RANK_BET = 38;               //排行活动下注
    const RANK_WATER = 39;             //排行活动流水
    const INNER_RECHARGE_GIVE = 40;    //内部充值赠送
    const OUTSIDE_RECHARGE_GIVE = 41;  //外部充值赠送
    const AUDITBET_SCORE_ADDITION = 42; //手动增加稽核
    const AUDITBET_SCORE_SUBTRACTION  = 43; //手动减少稽核
    const WASH_CODE_SCORE  = 45;     //洗码
    const WITHDRAWAL_REFUSE = 46;    //提现拒绝
    const AGENT_BROKERAGE = 50;      //代理佣金
    const FIRST_CHARGE_SIGNIN = 51;  //首充签到
    const ANSWER_GIVE_TYPE = 52;     //答题活动
    const WEEK_CASH_GIFT = 53;       //周礼金
    const MONTH_CASH_GIFT = 54;      //月礼金
    const PROMOTION_CASH_GIFT = 55;  //晋级礼金

    const TYPEID = [
        //self::SYSTEM_GIVE_TYPE           => '后台赠送',
        self::SYSTEM_GIVE_UP               => '上分',
        self::SYSTEM_GIVE_DOWN             => '下分',
        self::REGISTER_GIVE_TYPE           => '注册赠送',
        self::TRANSFER_TYPE                => '主动转账',
        self::COLLECT_TYPE                 => '接收转账',
        //self::BUY_PROP_TYPE                => '购买道具',
        //self::DIAMOND_EXCHANGE_TYPE        => '砖石兑换',
        self::BANK_DEPOSIT_TYPE            => '银行存款',
        self::BANK_WITHDRAWAL_TYPE         => '银行取款',
        self::BANK_WITHDRAWAL_REVENUE_TYPE => '银行取款税收',
        self::BACKPACK_TYPE                => '代理赠送',
        self::WITHDRAWAL_TYPE              => '提现',
        self::PAY_TYPE                     => '外部充值',
        self::INNER_PAY_TYPE               => '内部充值',
        self::SIGN_IN_TYPE                 => '签到赠送',
        self::WITHDRAWAL_BACK              => '提现失败',
        self::BIND_GIVE                    => '绑定赠送',
        self::ACTIVITY_DISH                => '转盘赠送',
        self::VIP_BUSINESS                 => '商人充值',
        self::RED_PACKET                   => '红包赠送',
        self::CHANNEL_PAY                  => '渠道充值',
        self::VIP_WITHDRAWAL               => '商人提现',
        self::VIP_WITHDRAWAL_BACK          => '商人提现扣除',
        self::FIRST_PAY_TYPE               => '首充赠送',
        self::AGENT_RED_PACKET             => '代理红包',
        self::PLAYERS_VIP_CASH             => 'VIP礼金',
        self::STREAM_SCORE_REBATE          => '流水返利',
        self::BET_RETURN_REBATE            => '下注返利',
        self::PROFIT_RETURN_REBATE         => '盈利返利',
        self::LOSS_RETURN_REBATE           => '回血返利',
        self::TASK_ACTIVITY                => '任务活动',
        self::TASK_ACTIVITY_ADDITION       => 'VIP任务加成',
        self::STREAM_SCORE_ADDITION        => 'VIP流水加成',
        self::BET_RETURN_ADDITION          => 'VIP下注加成',
        self::PROFIT_RETURN_ADDITION       => 'VIP盈利加成',
        self::LOSS_RETURN_ADDITION         => 'VIP回血加成',
        self::RANK_BET                     => '排行活动下注',
        self::RANK_WATER                   => '排行活动流水',
        self::INNER_RECHARGE_GIVE          => '内部充值赠送',
        self::OUTSIDE_RECHARGE_GIVE        => '外部充值赠送',
        self::AUDITBET_SCORE_ADDITION      => '手动增加稽核',
        self::AUDITBET_SCORE_SUBTRACTION   => '手动减少稽核',
        self::WASH_CODE_SCORE              => '洗码',
        self::AGENT_BROKERAGE              => '代理佣金',
        self::FIRST_CHARGE_SIGNIN          => '首充签到',
        self::ANSWER_GIVE_TYPE             => '答题活动',
        self::WEEK_CASH_GIFT               => '周礼金',
        self::MONTH_CASH_GIFT              => '月礼金',
        self::PROMOTION_CASH_GIFT          => '晋级礼金',
        self::WITHDRAWAL_REFUSE            => '提现拒绝',

    ];

    //个人中心统计充值类型
    const RECHARGE_TOTAL = [
        self::PAY_TYPE,       //外部充值
        self::INNER_PAY_TYPE  //内部充值
    ];

   //系统报表中的vip加成类型
    const VIP_ADDITION =[
        self::TASK_ACTIVITY_ADDITION ,//VIP任务加成
        self::LOSS_RETURN_ADDITION   ,//VIP回血加成
    ];
    //系统报表中的vip礼金类型
    const VIP_CASH_GIFT =[
        self::PLAYERS_VIP_CASH ,//VIP礼金
        self::WEEK_CASH_GIFT,   //周礼金
        self::MONTH_CASH_GIFT,  //月礼金
        self::PROMOTION_CASH_GIFT,//晋级礼金
    ];
    /**
     * 给客户端的流水类型，与后台(包括顺序)保持一致，屏蔽一些项
     * @param bool $getKey  获取key
     * @return array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function ClientType($getKey = false,$num=1)
    {
        $type = self::getTypes($num);
        $type_ids= [];
        foreach ($type as $k=>$v){
            $type_ids[' '.$k ]= $v;
        }
        if($getKey) {
            array_push($type_ids, 0); // 金币上下分
            return array_keys($type_ids);
        }
        return $type_ids;
    }
    /**
     * 类型，从数据库中获取
     * 公共方法传值，1,2,4
     * 1：全部显示(用户金币流水);
     * 2：所有礼金类型(运营系统-数据分析-礼金,客户端-账户明细-礼金合计包含类型,首页和所有报表中统计礼金);
     * 4：客户端—账户明细-流水类型
     */
    public static function getTypes($num=1){
        $data =Dict::where('pid',2)->select('name','extend')->where(\DB::raw("status & ".$num),'>',0)->orderBy('sort','asc')->pluck('name','extend')->toArray();
        return $data;
    }
	/*游戏记录--用户关联*/
	public function account()
	{
		return $this->belongsTo(AccountsInfo::class, 'UserID', 'UserID');
	}

	public function getTypeTextAttribute()
	{
	    if($this->TypeID == 0 && $this->ChangeScore >=0)
        {
            return self::TYPEID[self::SYSTEM_GIVE_UP];
        }elseif($this->TypeID == 0 && $this->ChangeScore <0)
        {
            return self::TYPEID[self::SYSTEM_GIVE_DOWN];
        }else{
            return self::TYPEID[$this->TypeID] ?? '其他';
        }

	}

	public function admin()
	{
		return $this->hasOne(AdminUser::class, 'id', 'MasterID');
	}

	/**
	 * 添加流水记录
	 *
	 * @param int $UserID         用户id
	 * @param int $CurScore       当前金币数
	 * @param int $CurInsureScore 保险箱存款金币
	 * @param int $ChangeScore    变化值
	 * @param int $TypeID         记录类型
	 * @param int $MasterID       管理员id
     * @param string $Reason      备注
     * @param int $OrderNo        订单号
	 *
	 * @return bool
	 */
	public static function addRecord($UserID, $CurScore, $CurInsureScore, $ChangeScore, $TypeID, $MasterID = 0,$Reason = '',$OrderNo = '',$CurAuditBetScore = 0)
	{
		$model = new self();
		$model->SerialNumber = 'no' . msectime() . rand(1000, 9999);
		$model->MasterID = $MasterID;
		$model->UserID = $UserID;
		$model->TypeID = $TypeID;
		$model->CurScore = $CurScore;
		$model->CurInsureScore = $CurInsureScore;
		$model->ChangeScore = $ChangeScore;
		$model->ClientIP = Request::getClientIp() ?? '0.0.0.0';
		$model->CollectDate = Carbon::now();
		$model->Reason = $Reason;
        $model->OrderID = $OrderNo;
        $model->CurAuditBetScore = $CurAuditBetScore;
		if ($model->save()) {
			return true;
		}

		return false;
	}

	public static function addRecordReturnID($UserID, $CurScore, $CurInsureScore, $ChangeScore, $TypeID, $MasterID = 0,$CurAuditBetScore = 0)
	{
		$model = new self();
        $model->SerialNumber = 'no' . msectime() . rand(1000, 9999);
        $model->MasterID = $MasterID;
		$model->UserID = $UserID;
		$model->TypeID = $TypeID;
		$model->CurScore = $CurScore;
		$model->CurInsureScore = $CurInsureScore;
		$model->ChangeScore = $ChangeScore;
		$model->ClientIP = Request::getClientIp() ?? '0.0.0.0';
		$model->CollectDate = Carbon::now();
        $model->CurAuditBetScore = $CurAuditBetScore;
		if ($model->save()) {
			return $model->SerialNumber;
		}
		return false;
	}

	/**
	 * 报表 - 获取各类型礼金
	 *
	 * @param array $timeData [startTime,endTime] datetime格式
	 * @param string $unit    (day|mouth) 按日或月统计
	 */
	public static function getCashGifts($start_date,$end_date,$client_type)
	{
		$model = new self();
		$basic_sql = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin($model::tableName().' as b','a.UserID','=','b.UserID')
            ->where('a.IsAndroid',0)
            ->where('b.ChangeScore', '>', 0)
            ->andFilterBetweenWhere('b.CollectDate',$start_date,$end_date)
            ->andFilterWhere('a.ClientType',$client_type);
        $list = (clone $basic_sql)->select('b.TypeID',
                \DB::raw('SUM(b.ChangeScore) as ChangeScore'),//操作变化金币
                \DB::raw('COUNT(DISTINCT b.UserID) as total')//领取人数
            )
            ->whereIn('b.TypeID', array_keys(RecordTreasureSerial::getTypes(2)))
            ->groupBy('b.TypeID')
            ->get();
        //总礼金和人数
        $list1 = (clone $basic_sql)->select(\DB::raw('COUNT(DISTINCT b.UserID) as total'))//领取人数
            ->whereIn('b.TypeID', array_keys(RecordTreasureSerial::getTypes(2)))
            ->first();
        //vip加成总礼金和人数
        $list2 = (clone $basic_sql)->select(
                \DB::raw('SUM(b.ChangeScore) as ChangeScore'),//操作变化金币
                \DB::raw('COUNT(DISTINCT b.UserID) as total')//领取人数
            )
            ->whereIn('b.TypeID', RecordTreasureSerial::VIP_ADDITION)
            ->first();
        //玩家vip总礼金和人数
        $list3 = (clone $basic_sql)->select(
                \DB::raw('SUM(b.ChangeScore) as ChangeScore'),//操作变化金币
                \DB::raw('COUNT(DISTINCT b.UserID) as total')//领取人数
            )
            ->whereIn('b.TypeID', RecordTreasureSerial::VIP_CASH_GIFT)
            ->first();
        $sum_give_cg = collect($list->toArray())->sum('ChangeScore');
        $sum_give_cg_num = $list1['total'];
        $sum_vip_addition_cg = $list2['ChangeScore'];
        $sum_vip_addition_cg_num = $list2['total'];
        $sum_players_vip_cg = $list3['ChangeScore'];
        $sum_players_vip_cg_num = $list3['total'];
        $list = collect($list->toArray())->groupBy('TypeID');
        $data = [
            'sum_give_cg'               => realCoins($sum_give_cg ?? '0.00'),//活动总礼金
            'sum_give_cg_coin'          => $sum_give_cg,
            'sum_give_cg_total'         => $sum_give_cg_num ?? 0,//活动礼金总人数
            'sum_vip_addition_cg'       => realCoins($sum_vip_addition_cg ?? '0.00'),//vip总加成
            'sum_vip_addition_total'    => $sum_vip_addition_cg_num ?? 0,//vip加成总人数
            'sum_players_vip_cg'        => realCoins($sum_players_vip_cg ?? '0.00'), //玩家vip总礼金
            'sum_players_vip_total'     => $sum_players_vip_cg_num ?? 0,//总玩家vip总礼金领取人数
            'register_cg'               => realCoins($list[self::REGISTER_GIVE_TYPE][0]['ChangeScore'] ?? '0.00'), //注册礼金
            'register_total'            => $list[self::REGISTER_GIVE_TYPE][0]['total'] ?? 0,            //注册礼金领取人数
            'sign_in_cg'                => realCoins($list[self::SIGN_IN_TYPE][0]['ChangeScore'] ?? '0.00'),       //签到礼金
            'sign_in_total'             => $list[self::SIGN_IN_TYPE][0]['total'] ?? 0,                  //签到礼金领取人数
            'bind_cg'                   => realCoins($list[self::BIND_GIVE][0]['ChangeScore'] ?? '0.00'),          //绑定礼金
            'bind_total'                => $list[self::BIND_GIVE][0]['total'] ?? 0,                     //绑定礼金领取人数
            'activity_dish_cg'          => realCoins($list[self::ACTIVITY_DISH][0]['ChangeScore']?? '0.00') ,      //转盘礼金
            'activity_dish_total'       => $list[self::ACTIVITY_DISH][0]['total'] ?? 0,                 //转盘礼金领取人数
            'red_packet_cg'             => realCoins($list[self::RED_PACKET][0]['ChangeScore'] ?? '0.00'),         //红包礼金
            'red_packet_total'          => $list[self::RED_PACKET][0]['total'] ?? 0,                    //红包礼金领取人数
            'first_pay_cg'              => realCoins($list[self::FIRST_PAY_TYPE][0]['ChangeScore'] ?? '0.00'),     //首充赠送
            'first_pay_total'           => $list[self::FIRST_PAY_TYPE][0]['total'] ?? 0,                //首充赠送领取人数
            'task_cg'                   => realCoins($list[self::TASK_ACTIVITY][0]['ChangeScore'] ?? '0.00'),     //任务赠送
            'task_total'                => $list[self::TASK_ACTIVITY][0]['total'] ?? 0,                //任务赠送领取人数
           //'stream_rebate_cg'          => realCoins($list[self::STREAM_SCORE_REBATE][0]['ChangeScore'] ?? '0.00'),     //流水返利赠送
           //'stream_rebate_total'       => $list[self::STREAM_SCORE_REBATE][0]['total'] ?? 0,                //流水返利领取人数
           //'bet_rebate_cg'             => realCoins($list[self::BET_RETURN_REBATE][0]['ChangeScore'] ?? '0.00'),     //下注返利赠送
           //'bet_rebate_total'          => $list[self::BET_RETURN_REBATE][0]['total'] ?? 0,                //下注返利赠送领取人数
           //'profit_rebate_cg'          => realCoins($list[self::PROFIT_RETURN_REBATE][0]['ChangeScore'] ?? '0.00'),     //盈利返利赠送
           //'profit_rebate_total'       => $list[self::PROFIT_RETURN_REBATE][0]['total'] ?? 0,                //盈利返利赠送领取人数
            'loss_rebate_cg'            => realCoins($list[self::LOSS_RETURN_REBATE][0]['ChangeScore'] ?? '0.00'),     //回血返利赠送
            'loss_rebate_total'         => $list[self::LOSS_RETURN_REBATE][0]['total'] ?? 0,                //回血返利赠送领取人数
            'task_addition_cg'          => realCoins($list[self::TASK_ACTIVITY_ADDITION][0]['ChangeScore'] ?? '0.00'),     //VIP任务加成赠送
            //'task_addition_total'       => $list[self::TASK_ACTIVITY_ADDITION][0]['total'] ?? 0,                //VIP任务加成赠送领取人数
            'loss_addition_cg'          => realCoins($list[self::LOSS_RETURN_ADDITION][0]['ChangeScore'] ?? '0.00'),     //VIP回血返利加成赠送
            //'loss_addition_total'       => $list[self::LOSS_RETURN_ADDITION][0]['total'] ?? 0,                //VIP回血返利加成赠送领取人数
            //'stream_addition_cg'        => realCoins($list[self::STREAM_SCORE_ADDITION][0]['ChangeScore'] ?? '0.00'),     //VIP流水返利加成赠送
            //'stream_addition_total'     => $list[self::STREAM_SCORE_ADDITION][0]['total'] ?? 0,                //VIP流水返利加成领取人数
            //'bet_addition_cg'           => realCoins($list[self::BET_RETURN_ADDITION][0]['ChangeScore'] ?? '0.00'),     //VIP下注返利加成赠送
            //'bet_addition_total'        => $list[self::BET_RETURN_ADDITION][0]['total'] ?? 0,                //VIP下注返利加成赠送领取人数
            //'profit_addition_cg'        => realCoins($list[self::PROFIT_RETURN_ADDITION][0]['ChangeScore'] ?? '0.00'),     //VIP盈利返利加成赠送
            //'profit_addition_total'     => $list[self::PROFIT_RETURN_ADDITION][0]['total'] ?? 0,                //VIP盈利返利加成赠送领取人数
            'rank_bet_cg'               => realCoins($list[self::RANK_BET][0]['ChangeScore'] ?? '0.00'), //排行活动下注
            'rank_bet_total'            => $list[self::RANK_BET][0]['total'] ?? 0,            //排行活动下注领取人数
            'rank_water_cg'             => realCoins($list[self::RANK_WATER][0]['ChangeScore'] ?? '0.00'), //排行活动流水
            'rank_water_total'          => $list[self::RANK_WATER][0]['total'] ?? 0,            //排行活动流水领取人数
            'inner_give_cg'             => realCoins($list[self::INNER_RECHARGE_GIVE][0]['ChangeScore'] ?? '0.00'), //内部充值赠送
            'inner_give_total'          => $list[self::INNER_RECHARGE_GIVE][0]['total'] ?? 0,            //内部充值赠送领取人数
            'outside_give_cg'           => realCoins($list[self::OUTSIDE_RECHARGE_GIVE][0]['ChangeScore'] ?? '0.00'), //外部充值赠送
            'outside_give_total'        => $list[self::OUTSIDE_RECHARGE_GIVE][0]['total'] ?? 0,            //外部充值赠送领取人数
            'wash_code_cg'              => realCoins($list[self::WASH_CODE_SCORE][0]['ChangeScore'] ?? '0.00'), //洗码赠送
            'wash_code_total'           => $list[self::WASH_CODE_SCORE][0]['total'] ?? 0,            //洗码赠送领取人数
            'agent_red_packet_cg'       => realCoins($list[self::AGENT_RED_PACKET][0]['ChangeScore'] ?? '0.00'), //代理红包
            'agent_red_packet_total'    => $list[self::AGENT_RED_PACKET][0]['total'] ?? 0,            //代理红包领取人数
            'first_charge_signin_cg'    => realCoins($list[self::FIRST_CHARGE_SIGNIN][0]['ChangeScore'] ?? '0.00'), //首充签到
            'first_charge_signin_total' => $list[self::FIRST_CHARGE_SIGNIN][0]['total'] ?? 0,            //首充签到领取人数
            'answer_give_type_cg'       => realCoins($list[self::ANSWER_GIVE_TYPE][0]['ChangeScore'] ?? '0.00'), //答题活动
            'answer_give_type_total'    => $list[self::ANSWER_GIVE_TYPE][0]['total'] ?? 0,            //答题活动领取人数
            'players_vip_cg'            => realCoins($list[self::PLAYERS_VIP_CASH][0]['ChangeScore'] ?? '0.00'),     //玩家vip礼金
            'week_cg'                   => realCoins($list[self::WEEK_CASH_GIFT][0]['ChangeScore'] ?? '0.00'),     //周礼金
            'month_cg'                  => realCoins($list[self::MONTH_CASH_GIFT][0]['ChangeScore'] ?? '0.00'),     //月礼金
            'promotion_cg'              => realCoins($list[self::PROMOTION_CASH_GIFT][0]['ChangeScore'] ?? '0.00'),     //晋级礼金

        ];
		return $data;
	}
	public function order(){
	    if(in_array($this->TypeID,[self::WITHDRAWAL_TYPE,self::WITHDRAWAL_BACK,self::VIP_WITHDRAWAL,self::VIP_WITHDRAWAL_BACK])){
            return $this->belongsTo(WithdrawalOrder::class,'OrderID','id');
        }else{
            return $this->belongsTo(PaymentOrder::class,'OrderID','id');
        }

    }
}

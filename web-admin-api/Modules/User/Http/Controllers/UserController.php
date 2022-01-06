<?php
/*用户*/
namespace Modules\User\Http\Controllers;
use App\Exceptions\NewException;
use Composer\Util\Platform;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SelectGameIdRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Models\Accounts\AccountsInfo;
use Models\Accounts\IndividualDatum;
use Models\Accounts\SystemStatusInfo;
use Models\AdminPlatform\AccountLog;
use Models\AdminPlatform\AccountsSet;
use Models\AdminPlatform\GameControlLog;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SubPermissions;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentRelation;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordDrawInfo;
use Models\Treasure\RecordDrawScore;
use Models\Treasure\RecordGameScore;
use Models\Treasure\UserAuditBetInfo;
use Modules\User\Http\Requests\ResetPasswordRequest;
use Transformers\AccountsLogTransformer;
use Transformers\AuditBetScoreTransformer;
use Transformers\RecordDrawScoreTransformer;
use Transformers\UserManageTransformer;
use function foo\func;

class UserController extends BaseController
{
    const SORT_TYPE = [
        0   =>  '注册时间',
        1   =>  '登录时间',
        2   =>  '投注递减',
        3   =>  '投注递增',
        4   =>  '盈利递减',
        5   =>  '盈利递增',
        6   =>  '充值递增',
        7   =>  '充值递减',
        8   =>  '提现递增',
        9   =>  '提现递减',
        10  =>  '活动礼金递增',
        11  =>  '活动礼金递减',
        12  =>  '游戏天数递增',
        13  =>  '游戏天数递减',
    ];
    /**
     * 玩家列表
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getList(SelectGameIdRequest $request)
    {
        try{
            $date_type  = $request->input('date_type',1);
            $start_date = request('start_date','');
            $end_date   = request('end_date','');
            $search_type = request('search_type','mobile_phone'); // mobile_phone || bank_card || real_name
            $search_content = request('search_content','');
            $whereStr = '';
            $whereStr2 = '';
            $whereStr3 = '';
            $whereStr4 = '';
            if ($date_type == 2) {
                if ($start_date){
                    $whereStr  .= " AND UpdateDate >= '" . date('Y-m-d 00:00:00',strtotime($start_date)) . "'";
                    $whereStr2 .= " AND created_at >= '" . date('Y-m-d 00:00:00',strtotime($start_date)) . "'";
                    $whereStr3 .= " AND CollectDate >= '" . date('Y-m-d 00:00:00',strtotime($start_date)) . "'";
                    $whereStr4 .= " AND UpdateTime >= '" . date('Y-m-d 00:00:00',strtotime($start_date)) . "'";
                }
                if ($end_date){
                    $whereStr  .= " AND UpdateDate <= '" . date('Y-m-d 23:59:59',strtotime($end_date)) . "'";
                    $whereStr2 .= " AND created_at <= '" . date('Y-m-d 23:59:59',strtotime($end_date)) . "'";
                    $whereStr3 .= " AND CollectDate <= '" . date('Y-m-d 23:59:59',strtotime($end_date)) . "'";
                    $whereStr4 .= " AND UpdateTime <= '" . date('Y-m-d 23:59:59',strtotime($end_date)) . "'";
                }
            }
            //return $whereStr;
            $fields = [
                \DB::raw("(SELECT COUNT(*) FROM admin_platform.dbo.payment_orders WHERE payment_status='".PaymentOrder::SUCCESS."' AND user_id=a.UserID " .$whereStr2. ") AS pay_num"),
                \DB::raw("(SELECT SUM(amount) FROM admin_platform.dbo.payment_orders WHERE payment_status='".PaymentOrder::SUCCESS."' AND user_id=a.UserID " .$whereStr2. ") AS pay_money"),
                \DB::raw("(SELECT COUNT(*) FROM admin_platform.dbo.withdrawal_orders WHERE status=".WithdrawalOrder::PAY_SUCCESS." AND user_id=a.UserID " .$whereStr2. ") AS withdrawal_num"),
                \DB::raw("(SELECT SUM(money) FROM admin_platform.dbo.withdrawal_orders WHERE status=".WithdrawalOrder::PAY_SUCCESS." AND user_id=a.UserID " .$whereStr2. ") AS withdrawal_money"),
                \DB::raw("(SELECT SUM(ChangeScore) FROM WHQJRecordDB.dbo.RecordTreasureSerial WHERE TypeID IN(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).") AND UserID=a.UserID " .$whereStr3. ") AS active_money"),
                \DB::raw("(SELECT SUM(StreamScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ") AS spring_water"),//流水
                \DB::raw("(SELECT SUM(ChangeScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ") AS win_lose"),//玩家输赢
                \DB::raw("(SELECT SUM(JettonScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ") AS bet"),//筛选时间有效投注
                \DB::raw("(SELECT SUM(ValidJettonScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID) AS sum_bet"),//总投注
                \DB::raw("(SELECT COUNT(*) FROM WHQJRecordDB.dbo.RecordUserLogon WHERE UserID=a.UserID) AS login_days"),
                'a.UserID','a.GameID','a.Accounts','a.NickName','a.ClientType','a.LogonMode', 'a.PlatformID','a.RegisterIP','a.RegisterDate', 'a.RegisterMobile','a.OnLineTimeCount',
                'a.PlayTimeCount','a.GameLogonTimes', 'a.LastLogonIP','a.LastLogonDate','a.RegDeviceName','a.SystemVersion','b.nullity','b.withdraw','c.JettonScore','c.WinScore','c.CurJettonScore','c.Score','d.channel_id','e.AuditBetScore','a.BankCardID','f.Compellation'
            ];
            $gameIds = explode(',',request('game_id',0));
            $list = AccountsInfo::from('AccountsInfo as a')
                ->select($fields)
                ->leftJoin(AccountsSet::tableName().' as b','a.UserID','=','b.user_id')
                ->leftJoin(GameScoreInfo::tableName().' as c','a.UserID','=','c.UserID')
                ->leftJoin(ChannelUserRelation::tableName().' as d','a.UserID','=','d.user_id')
                ->leftJoin(UserAuditBetInfo::tableName().' as e','a.UserID','=','e.UserID')
                ->leftJoin(IndividualDatum::tableName().' as f','a.UserID','=','f.UserID')
                ->where('a.IsAndroid',0)
                ->where(function($query) use ($search_content, $search_type) {
                    if($search_content){
                        if ($search_type == 'mobile_phone'){
                            $query->where('a.RegisterMobile', $search_content);
                        } else if ($search_type == 'bank_card'){
                            $query->where('a.BankCardID', $search_content);
                        } else if ($search_type == 'real_name'){
                            $query->where('f.Compellation', $search_content);
                        }
                    }
                })
//                ->andFilterWhere('a.GameID', intval(request('game_id')))//game_id查询
                ->when($request->game_id,function ($query)use($gameIds){
                    $query->whereIn('GameID',$gameIds);
                })
                ->andFilterWhere('a.Accounts', request('Accounts'))//账号查询
                ->andFilterWhere('a.NickName', request('nickname'))//昵称查询
                //->andFilterWhere('a.RegisterMobile', request('RegisterMobile'))//手机号查询
                ->andFilterWhere('a.PlatformID', request('platform_id'));//平台查询
            if($date_type == 1){
                $list->andFilterBetweenWhere('a.RegisterDate',$start_date,$end_date);
            }elseif ($date_type == 2){
                $list->whereExists(function ($query) {
                    $query->select(\DB::raw(1))->from(RecordUserLogon::tableName().' as e')->whereRaw('e.UserID=a.UserID');
                    $s = request('start_date','');
                    $e = request('end_date','');
                    if($s){ $query->where('CreateDate','>=',$s); }
                    if($e){ $query->where('CreateDate','<=',$e); }
                });
            }
            $list = $this->searchStatus($list, request('status_type'));
            //排序
            switch (\request('sort')){
                case 0:
                    $list = $list->orderBy('a.RegisterDate','desc');//注册时间倒序
                    break;
                case 1:
                    $list = $list->orderBy('a.LastLogonDate','desc');//登录时间倒序
                    break;
                case 2:
                    if ($date_type == 2){
                        $list = $list->orderByRaw("(SELECT SUM(JettonScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ") desc");
                    }else{
                        $list = $list->orderBy('c.JettonScore','desc');//投注递减
                    }
                    break;
                case 3:
                    if ($date_type == 2){
                        $list = $list->orderByRaw("(SELECT SUM(JettonScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ")");
                    }else{
                        $list = $list->orderBy('c.JettonScore','asc');//投注递增
                    }
                    break;
                case 4:
                    if ($date_type == 2){
                        $list = $list->orderByRaw("(SELECT SUM(ChangeScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ") desc");
                    }else{
                        $list = $list->orderBy('c.WinScore','desc');//盈利递减
                    }
                    break;
                case 5:
                    if ($date_type == 2){
                        $list = $list->orderByRaw("(SELECT SUM(ChangeScore) FROM WHQJTreasureDB.dbo.RecordGameScore WHERE UserID=a.UserID " .$whereStr4. ")");
                    }else{
                        $list = $list->orderBy('c.WinScore','asc');//盈利递增
                    }
                    break;
                case 6:
                    //充值递增
                    $list = $list->orderByRaw("(SELECT SUM(amount) FROM admin_platform.dbo.payment_orders WHERE payment_status='".PaymentOrder::SUCCESS."' AND user_id=a.UserID " .$whereStr2. ")");
                    break;
                case 7:
                    //充值递减
                    $list = $list->orderByRaw("(SELECT SUM(amount) FROM admin_platform.dbo.payment_orders WHERE payment_status='".PaymentOrder::SUCCESS."' AND user_id=a.UserID " .$whereStr2. ") DESC");
                    break;
                case 8:
                    //递增
                    $list = $list->orderByRaw("(SELECT SUM(money) FROM admin_platform.dbo.withdrawal_orders WHERE status=".WithdrawalOrder::PAY_SUCCESS." AND user_id=a.UserID " .$whereStr2. ")");
                    break;
                case 9:
                    //递减
                    $list = $list->orderByRaw("(SELECT SUM(money) FROM admin_platform.dbo.withdrawal_orders WHERE status=".WithdrawalOrder::PAY_SUCCESS." AND user_id=a.UserID " .$whereStr2. ") DESC");
                    break;
                case 10:
                    //活动礼金递增
                    $list = $list->orderByRaw("(SELECT SUM(ChangeScore) FROM WHQJRecordDB.dbo.RecordTreasureSerial WHERE TypeID IN(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).") AND UserID=a.UserID " .$whereStr3. ")");
                    break;
                case 11:
                    //活动礼金递减
                    $list = $list->orderByRaw("(SELECT SUM(ChangeScore) FROM WHQJRecordDB.dbo.RecordTreasureSerial WHERE TypeID IN(".implode(',',array_keys(RecordTreasureSerial::getTypes(2))).") AND UserID=a.UserID " .$whereStr3. ") DESC");
                    break;
                case 12:
                    //游戏天数递增
                    $list = $list->orderByRaw("(SELECT COUNT(*) FROM WHQJRecordDB.dbo.RecordUserLogon WHERE UserID=a.UserID )");
                    break;
                case 13:
                    //游戏天数递减
                    $list = $list->orderByRaw("(SELECT COUNT(*) FROM WHQJRecordDB.dbo.RecordUserLogon WHERE UserID=a.UserID ) DESC");
                    break;
                default:
                    $list = $list->orderBy('a.RegisterDate','desc');//注册时间倒序
                    break;
            }
            //分页
            $list = $list->paginate(30);
            //手机号显示权限，1超管都显示，2非超管需要勾选
            $isShowPhone = 0;//不显示
            if (Auth::guard('admin')->user()->super()){
                $isShowPhone = 1;//显示
            }else{
                if (SubPermissions::where('admin_id',$this->user()->id)->where('sign','show_phone')->first()){
                    $isShowPhone = 1;//显示
                }
            }
            foreach ($list as $k => $v){
                $list[$k]['parent_game_id'] = $this->getParentGameId($v['UserID']);//父级id
                if($list[$k]['parent_game_id']> 0 ||  isset($list[$k]['channel'])){
                    $list[$k]['bind_channel'] = 1;
                }else{
                    $list[$k]['bind_channel'] = 0;
                }
                //登录时间筛选时输赢、打码量、流水
                if ($date_type == 2){
                    $list[$k]['JettonScore'] = $list[$k]['bet'];
                    $list[$k]['WinScore']    = $list[$k]['win_lose'];
                }
                //手机号显示判断
                if ($isShowPhone == 0 && !empty($v['RegisterMobile'])){
                    $list[$k]['RegisterMobile']  = substr_replace($v['RegisterMobile'],'****',3,4);
                }
            }
            return $this->response->paginator($list, new UserManageTransformer())->addMeta('count', self::SORT_TYPE);
        }catch (\Exception $exception){\Log::info($exception);
            return ResponeFails('操作异常');
        }
    }


    /**
     * 玩家信息
     *
     * @param $id
     *
     */
    public function getDetails()
    {
        $user_id = intval(request('user_id'));
        //玩家基本信息
        $user = AccountsInfo::with(['accountset','GameScoreInfo','AuditBetInfo'])->find($user_id);
        if (!$user) {
            return ResponeFails('用户不存在');
        }
        $user->RegisterDate =date('Y-m-d H:i:s',strtotime($user['RegisterDate']));
        //查询用户状态信息
        $user->status = AccountsSet::select('nullity', 'withdraw')->where('user_id', $user_id)->first() ?? [
                'nullity'  => 0,
                'withdraw' => 0,
            ];
        //账户余额
        $user->balance = realCoins($user->GameScoreInfo['Score']) ?? 0;
        //银行存款
        $user->brank_balance = realCoins($user->GameScoreInfo['InsureScore']) ?? 0;
        //推广余额
        $user->promotion_balance = realCoins($this->getUserAgentScore(request('user_id')));
        //查看玩家银行信息（查询记录的最后一条）
        $user->bank = WithdrawalOrder::where('user_id', $user_id)->orderBy('id', 'desc')->first() ?? null;
        //总充值次数
        $user->all_pay_count = $this->getPayTimes($user_id);
        //总充值
        $user->all_pay_sum = realCoins($this->getPaySum($user_id));
        //总次数
        $user->all_withdraw_count = $this->getWithdrawTimes($user_id);

        $user->all_withdraw_sum = realCoins($this->getWithdrawSum($user_id));
        //总输赢
        //$user->all_win_lose = realCoins($this->getWinloseSum($user_id));
        $user->all_win_lose = realCoins(RecordGameScore::sumWinLose($user_id) ?? 0);
        //当日充值
        $user->today_pay_sum = realCoins($this->getPaySum($user_id,true));
        //当日次数
        $user->today_withdraw_count = $this->getWithdrawTimes($user_id,true);
        //当日
        $user->today_withdraw_sum = realCoins($this->getWithdrawSum($user_id,true));
        //当日输赢
        //$user->today_win_lose = realCoins($this->getWinloseSum($user_id,true));
        $user->today_win_lose = realCoins(RecordGameScore::sumWinLose($user_id,true) ?? 0);
        //用户当日有效投注
        $user->today_water_sum = realCoins($this->getUserJettonScore($user_id,true,true));
        //用户总投注
        $user->sum_jetton_score = realCoins($this->getUserJettonScore($user_id,false,false));
        //用户综合稽核打码量
        $user->user_audit_sum = realCoins($user->AuditBetInfo['AuditBetScore'] ?? 0);
        //是否可以重置用户银行密码
        $user->isResetBankPass = empty(trim($user->InsurePass)) ? false : true;
        //用户有效投注(总)
        $user->bet_sum = realCoins($user->GameScoreInfo['JettonScore'] ?? 0);
        //用户当日下注量
        //$user->today_jetton_score_sum = realCoins($user->GameScoreInfo['CurJettonScore'] ?? 0);
        //用户审核打码量
        //$user->withdraw_audit_sum = realCoins($user->GameScoreInfo['CurJettonScore'] ?? 0);
        //基本资料
        $user->basic_info = IndividualDatum::where('UserID',$user->UserID)->first();

        return ResponeSuccess('请求成功', $user);

    }


    /**
     * 用户登录设置
     *
     * @param array $user_ids 用户id集合
     * @param int   $nullity  状态
     *
     */
    public function setNullity(Request $request)
    {
        $request->validate([
            'game_ids' => 'array|distinct',
            'nullity'  => 'integer|in:0,1',
            'remark'   => 'required|max:255',
            'type'     => 'integer|in:1,2',
        ]);
        $game_ids       = request('game_ids');
        $nullity        = request('nullity');
        //最多只能操作50个用户
        if (count($game_ids) > 50){
            return ResponeFails('最多只能操作50个用户');
        }
        try {
            $user_list = AccountsInfo::select('UserID','GameID','RegisterMobile','RegisterDate','ClientType')->whereIn('GameID',$game_ids)->get();
            foreach ($user_list as $k => $v){
                $res = AccountsSet::updateOrCreate(['user_id' => $v->UserID], ['nullity'=>$nullity]);
                $accountLog = new AccountLog();
                $accountLog->user_id        = $v->UserID;
                $accountLog->game_id        = $v->GameID;
                $accountLog->create_time    = date('Y-m-d H:i:s');
                $accountLog->re_time        = $v->RegisterDate;
                $accountLog->phone          = $v->RegisterMobile;
                $accountLog->client_type    = $v->ClientType;
                $accountLog->ip             = $request->ip();
                $accountLog->remark         = $request->input('remark','');
                $accountLog->type           = $request->input('type',1);
                $accountLog->admin_id       = $this->user()->id;
                $res2 = $accountLog->save();
                if (!$res){
                    return ResponeFails('用户：'.$v->GameID.'操作失败');
                }
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e) {
            return ResponeFails('操作异常');
        }

    }

    /**
     * 用户设置
     *
     */
    public function setWithdraw(Request $request)
    {
        $request->validate([
            'user_ids' => 'array|distinct',
            'withdraw' => 'integer|in:0,1',
            'type'     => 'integer|in:3,4',
            'remark'   => 'max:255',
        ]);
        $game_ids = request('game_ids');
        $withdraw = request('withdraw');
        //最多只能操作50个用户
        if (count($game_ids) > 50){
            return ResponeFails('最多只能操作50个用户');
        }
        try {
            $user_list = AccountsInfo::select('UserID','GameID','RegisterMobile','RegisterDate','ClientType')->whereIn('GameID',$game_ids)->get();
            foreach ($user_list as $k => $v){
                $res = AccountsSet::updateOrCreate(['user_id' => $v->UserID], ['withdraw'=>$withdraw]);
                $accountLog = new AccountLog();
                $accountLog->user_id        = $v->UserID;
                $accountLog->game_id        = $v->GameID;
                $accountLog->create_time    = date('Y-m-d H:i:s');
                $accountLog->re_time        = $v->RegisterDate;
                $accountLog->phone          = $v->RegisterMobile;
                $accountLog->client_type    = $v->ClientType;
                $accountLog->ip             = $request->ip();
                $accountLog->remark         = $request->input('remark','');
                $accountLog->type           = $request->input('type',3);
                $accountLog->admin_id       = $this->user()->id;
                $res2 = $accountLog->save();
                if (!$res){
                    return ResponeFails('用户：'.$v->GameID.'操作失败');
                }
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e) {
            return ResponeFails('操作异常');
        }
    }

    /**
     * 用户及登录状态设置
     *
     */
    public function setNullityWithdraw(Request $request)
    {
        $request->validate([
            'user_ids' => 'array|distinct',
            'withdraw' => 'integer|in:0,1',
            'nullity'  => 'integer|in:0,1',
            'type'     => 'integer|in:5,6',
            'remark'   => 'max:255',
        ]);
        $game_ids = request('game_ids');
        $withdraw = request('withdraw');
        $nullity  = request('nullity');
        //最多只能操作50个用户
        if (count($game_ids) > 50){
            return ResponeFails('最多只能操作50个用户');
        }
        try {
            $user_list = AccountsInfo::select('UserID','GameID','RegisterMobile','RegisterDate','ClientType')->whereIn('GameID',$game_ids)->get();
            foreach ($user_list as $k => $v){
                //更改及登录的状态
                $res = AccountsSet::updateOrCreate(['user_id' => $v->UserID], ['withdraw'=>$withdraw,'nullity'=>$nullity]);
                $accountLog = new AccountLog();
                $accountLog->user_id        = $v->UserID;
                $accountLog->game_id        = $v->GameID;
                $accountLog->create_time    = date('Y-m-d H:i:s');
                $accountLog->re_time        = $v->RegisterDate;
                $accountLog->phone          = $v->RegisterMobile;
                $accountLog->client_type    = $v->ClientType;
                $accountLog->ip             = $request->ip();
                $accountLog->remark         = $request->input('remark','');
                $accountLog->type           = $request->input('type',5);
                $accountLog->admin_id       = $this->user()->id;
                $res2 = $accountLog->save();
                if (!$res){
                    return ResponeFails('用户：'.$v->GameID.'操作失败');
                }
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e) {
            return ResponeFails('操作异常');
        }
    }

    /**
     * 玩家检测
     *
     */
    public function check()
    {
        try{
            if (!(new AccountsInfo())->getUserId(request('game_id'))) {
                return ResponeFails('用户不存在');
            }
            return ResponeSuccess('检测通过');
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 玩家日志
     *
     */
    public function gameLog()
    {
        Validator::make(request()->all(), [
            'user_id' => ['required', 'numeric'],
        ])->validate();
        /*$kind_id = request('kind_id');
        $list = RecordDrawScore::whereHas('darwInfo', function ($query) use ($kind_id) {
            if ($kind_id) {
                return $query->where('KindID', $kind_id);
            }
            return $query;
        })
            ->where('UserID', request('user_id'))
            ->orderBy('InsertTime', 'desc')
            ->paginate(config('page.list_rows'));*/
        $list = RecordDrawScore::from(RecordDrawScore::tableName().' AS a')
            ->select('a.*','b.KindID','b.ServerID','b.Waste','b.BankerCards','c.KindName','d.ServerName',
                \DB::raw('a.RewardScore-a.JettonScore AS winlose')
            )
            ->leftJoin(RecordDrawInfo::tableName().' AS b','a.DrawID','=','b.DrawID')
            ->leftJoin(GameKindItem::tableName().' AS c','b.KindID','=','c.KindID')
            ->leftJoin(GameRoomInfo::tableName().' AS d','b.ServerID','=','d.ServerID')
            ->andFilterWhere('a.UserID', request('user_id'))
            ->andFilterWhere('b.KindID', request('kind_id'))
            ->orderBy('a.InsertTime','desc')
            ->paginate(config('page.list_rows'));
        //dump($list);
        return $this->response->paginator($list, new RecordDrawScoreTransformer());
    }

    /**
     * 修改密码
     *
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $game_id= AccountsInfo::where('UserID',$request->input('user_id'))->value('GameID');
        try{
            $res = AccountsInfo::where('UserID',$request->input('user_id'))->update(['LogonPass'=>md5($request->input('password'))]);
            if (!$res){
                GameControlLog::addOne('修改密码', '给玩家：' .$game_id.' 修改密码',GameControlLog::CHANGE_PASSWORD,GameControlLog::FAILS);
                return ResponeFails('修改失败');
            }
        }catch (Exception $exception){
            GameControlLog::addOne('修改密码', '给玩家：' .$game_id.' 修改密码',GameControlLog::CHANGE_PASSWORD,GameControlLog::FAILS);
            return ResponeFails('修改失败');
        }
        GameControlLog::addOne('修改密码', '给玩家：' .$game_id.' 修改密码',GameControlLog::CHANGE_PASSWORD,GameControlLog::SUCCESS);
        return ResponeSuccess('修改成功');
    }

    /**
     * 刷新账户余额
     *
     */
    public function gameScore()
    {
        return ResponeSuccess('刷新成功', realCoins($this->getUserScore(request('user_id')) ?? 0));
    }

    /**
     * 刷新推广余额
     *
     */
    public function promotionScore()
    {
        return ResponeSuccess('刷新成功', realCoins($this->getUserAgentScore(request('user_id')) ?? 0));
    }

    /**
     * 登录状态、状态筛选
     *
     */
    protected function searchStatus(&$obj, $status_type)
    {
        switch ($status_type) {
            //全部启用
            case 1:
                $obj->where(function ($query) {
                    $query->orWhere(function ($query) {
                        $query->where('b.nullity', AccountsSet::NULLITY_ON)->where('b.withdraw', AccountsSet::WITHDRAW_ON);
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNull('b.nullity')->whereNull('b.withdraw');
                    });
                });
                break;
            //禁止登录
            case 2:
                $obj->where(function ($query) {
                    $query->where('b.nullity', AccountsSet::NULLITY_OFF);
                });
                break;
            //禁止
            case 3:
                $obj->where(function ($query) {
                    $query->where('b.withdraw', AccountsSet::WITHDRAW_OFF);
                });
                break;
            //全部禁止
            case 4:
                $obj->where(function ($query) {
                    $query->orWhere(function ($query) {
                        $query->where('b.nullity', AccountsSet::NULLITY_OFF)->where('b.withdraw', AccountsSet::WITHDRAW_OFF);
                    });
                });
                break;
        }
        return $obj;
    }

    /*
     * 用户绑定渠道时可选的所有渠道
     *
     */
    public function channelList()
    {
        $list = ChannelInfo::where('nullity',ChannelInfo::NULLITY_ON)->get(['channel_id','nickname']);
        return ResponeSuccess('请求成功', $list);
    }
    /*
    * 用户手动绑定渠道
    *
    */
    public function channelBind(Request $request)
    {
        Validator::make(request()->all(), [
            'user_id'            => ['required','numeric'],
            'channel_id'         => ['required','numeric'],
        ], [
            'user_id.numeric'    => '用户ID必传！',
            'channel_id.numeric' => '渠道ID必传！',
        ])->validate();
        $model1 = ChannelUserRelation::where('user_id',$request->input('user_id'))->first();
        if ($model1) {
            return ResponeFails('用户已绑定过渠道！');
        }
        $model2 = AgentRelation::where('user_id',$request->input('user_id'))->first();
        if($model2){
            if ($model2->parent_user_id > 0) {
                return ResponeFails('用户已绑定过代理！');
            }
        }
        $model= new ChannelUserRelation();
        $model -> channel_id   = $request->input('channel_id');
        $model -> user_id      = $request->input('user_id');
        $model -> created_at   = date('Y-m-d H:i:s',time());
        if($model->save())
        {
            return ResponeSuccess('渠道绑定成功');
        }
        return ResponeFails('渠道绑定失败');
    }
    //重置用户银行密码
    public function resetBackPass(Request $request)
    {
        try{
            $res = AccountsInfo::where('UserID',$request->input('user_id'))->update(['InsurePass'=>'']);
            if (!$res){
                return ResponeFails('重置失败');
            }
            return ResponeSuccess('重置成功');
        }catch (Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 批量封号
     *
     */
    public function logs(Request $request)
    {
        $request->validate([
            'game_id'       => 'nullable|integer',
            'type'          => 'nullable|integer|in:1,2,3,4,5,6',
            'remark'        => 'max:255',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
        ],[
            'game_id.integer'   => '用户id必须为整数',
            'type.integer'      => '操作类型必须为整数',
            'type.in'           => '操作类型不在范围内',
            'start_date.date'   => '时间格式有误',
            'end_date.date'     => '时间格式有误',
        ]);
        try{
            $list = AccountLog::from('account_log as a')
                ->select('a.*','b.username')
                ->andFilterWhere('a.phone',\request('phone'))
                ->leftJoin('admin_platform.dbo.admin_users as b','a.admin_id','=','b.id')
                ->andFilterWhere('a.type',\request('type'))
                ->andFilterWhere('a.game_id',\request('game_id'))
                ->andFilterBetweenWhere('a.create_time',request('start_date'),request('end_date'))
                ->orderBy('a.id','desc')
                ->paginate(config('page.list_rows'));
            return $this->response->paginator($list, new AccountsLogTransformer());
        }catch (\Exception $exception){
            return ResponeFails('异常错误');
        }
    }

    /**
     * 用户稽核列表
     *
     * @return \Dingo\Api\Http\Response
     */
    public function auditBetList(SelectGameIdRequest $request)
    {
        if(!$request->game_id){
            return ResponeFails('玩家ID为必填');
        }
        $UserID = (new AccountsInfo)->getUserId($request->game_id);
        if(!$UserID){
            return ResponeFails('玩家ID:'.$request->game_id.'不存在');
        }
        $data = $this->gameIdSearchUserId($request->game_id, new RecordTreasureSerial());
        $list = $data->with('order')
            ->orderBy('CollectDate', 'desc')
            ->paginate(config('page.list_rows'));
        $serial = RecordTreasureSerial::select(
            \DB::raw('sum(ChangeScore) as ScoreSum'),
            \DB::raw('count(UserID) as ScoreCount')
        )->where('UserID',$UserID)->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)))->first();
        $AuditBetScore = UserAuditBetInfo::where('UserID',$UserID)->value('AuditBetScore');
        $AuditBetTake = SystemStatusInfo::where('StatusName','AuditBetScoreTake')->value('StatusValue');
        $middle = [
            'ScoreSum' => realCoins($serial->ScoreSum),
            'ScoreCount' => $serial->ScoreCount,
            'AuditBetScore' => realCoins($AuditBetScore),
            'AuditBetTake' => bcdiv($AuditBetTake,100,1)
        ];
        return $this->response->paginator($list, new AuditBetScoreTransformer())->addMeta('middle',$middle);
    }

    /**
     * 增加或减少稽核打码
     *
     * @return \Dingo\Api\Http\Response
     */
    public function auditBetEdit(Request $request)
    {
        $request->validate([
            'game_ids'      => 'required|array',
            'type'          => 'required|integer|in:1,2',
            'auditBet_score'=> 'required|integer|min:1',
        ],[
            'game_ids.required' => '玩家ID为必填',
            'game_ids.array'    => '玩家ID应该传数组',
            'type.integer'      => '操作类型必须为整数',
            'type.in'           => '操作类型不在范围内',
            'auditBet_score.required'    => '打码数量必填',
            'auditBet_score.integer'     => '打码数量必须为整数',
            'auditBet_score.min'         => '打码数量最小为1',
        ]);
        try{
            $game_ids = request('game_ids',[]);
            if (count($game_ids) != count(array_unique($game_ids))) {
                throw new NewException('玩家ID不能重复');
            }
            if(count($game_ids) > 10){
                throw new NewException('玩家ID最多10个');
            }
            if(request('type') == 1){
                $auditBet_score =  request('auditBet_score') * realRatio();
                $type = RecordTreasureSerial::AUDITBET_SCORE_ADDITION;
                $action = GameControlLog::ADD_AUDITBET; //增加稽核的动作类型
                $title = '增加';
            }else{
                $auditBet_score =  bcsub(0,request('auditBet_score') * realRatio());
                $type = RecordTreasureSerial::AUDITBET_SCORE_SUBTRACTION;
                $action = GameControlLog::REDUCE_AUDITBET; //减少稽核的动作类型
                $title = '减少';
            }
            RecordTreasureSerial::beginTransaction([RecordTreasureSerial::connectionName()]);
            foreach ($game_ids as $game_id){
                $UserID = (new AccountsInfo)->getUserId($game_id);
                if(!$UserID){
                    RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName()]);
                    throw new NewException('玩家ID:'.$game_id.'不存在');
                }
                $score = GameScoreInfo::where('UserID', $UserID)->first();
               // RecordTreasureSerial::addRecord($UserID,$score->Score,$score->InsureScore,0,$type,Auth::guard('admin')->id(),request('Reason'),'',$auditBet_score);
                $UA = UserAuditBetInfo::where('UserID',$UserID)->first();
                if($UA){
                    $beforeScore = $UA->AuditBetScore;
                    $afterScore = $UA->AuditBetScore + $auditBet_score;
                    if($afterScore < 0){
                        if($beforeScore == 0){
                            throw new NewException('该玩家'.config('set.auditBet').'还剩'.realCoins($beforeScore));
                        }
                       // RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName()]);
                        $afterScore = 0;
                        $auditBet_score = $UA->AuditBetScore*(-1); //流水表
                    }
                    UserAuditBetInfo::where('UserID',$UserID)->update(['AuditBetScore' => $afterScore]);
                }else{
                    if($auditBet_score < 0){
                       // RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName()]);
                        throw new NewException('该玩家'.config('set.auditBet').'暂无');
                    }
                    UserAuditBetInfo::create(['UserID' => $UserID,'AuditBetScore' => $auditBet_score]);
                }
                RecordTreasureSerial::addRecord($UserID,$score->Score,$score->InsureScore,0,$type,Auth::guard('admin')->id(),request('Reason'),'',$auditBet_score);
            }
        }catch (NewException $e){
            RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName()]);
            GameControlLog::addOne($title.'稽核', '给玩家：' .implode(',',$game_ids) .' '.$title. '稽核，'.abs(realCoins($auditBet_score)),$action,GameControlLog::FAILS);
            return ResponeFails('操作失败：'.$e->getMessage());
        }catch (Exception $e){
            RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName()]);
            GameControlLog::addOne($title.'稽核', '给玩家：' .implode(',',$game_ids) .' '.$title. '稽核，'.abs(realCoins($auditBet_score)),$action,GameControlLog::FAILS);
            return ResponeFails('操作失败');
        }
        RecordTreasureSerial::commit([RecordTreasureSerial::connectionName()]);
        GameControlLog::addOne($title.'稽核', '给玩家：' .implode(',',$game_ids)  .' '.$title. '稽核，'.abs(realCoins($auditBet_score)),$action,GameControlLog::SUCCESS);
        return ResponeSuccess('操作成功');
    }

    /**
     * 配置活动稽核打码倍数
     *
     * @return \Dingo\Api\Http\Response
     */
    public function auditBetTake(Request $request)
    {
        $request->validate([
            'AuditBetTake' => 'required|numeric|min:0',
        ], [
            'AuditBetTake.required' => '打码倍数为必填',
            'AuditBetTake.numeric' => '打码倍数必须为数字',
            'AuditBetTake.min' => '打码倍数最小为0',
        ]);
        SystemStatusInfo::where('StatusName','AuditBetScoreTake')->update(['StatusValue' => bcdiv($request->AuditBetTake,1,1) * 100]);
        return ResponeSuccess('操作成功');
    }

    //刷新金币
    public function refreshGold($user_id,$type)
    {
        try {
            $t = msectime();
            $user_id = AccountsInfo::where('UserID', $user_id)->value('UserID');
            if(!$user_id) {
                throw new NewException('玩家不存在');
            }
            $client = new Client(['base_uri' => config('prots.outer_platform_api')]);
            if($type == 1) {
                $res = $client->request('GET', '/rpc/platform/balance/'.$user_id, ['timeout' => 10]);
                $result = \GuzzleHttp\json_decode($res->getBody());
                if ($result->code == 0) {
                    $return = json_decode(json_encode($result->data), true);
                    if(isset($return['platform_id'])) {
                        $return['name'] = OuterPlatform::where('id', $return['platform_id'])->value('name');
                    }
                    $return['msc'] = msectime() - $t;
                    return ResponeSuccess('Success', [$return]);
                }
                return ResponeFails('请求失败', $result->code);
            } else if($type == 2) {
                $data = [
                    'user_id' => $user_id
                ];
                $res = $client->request('POST', '/api/client/outer/logout', ['form_params' => $data, 'timeout' => 10]);
                $result = \GuzzleHttp\json_decode($res->getBody());
                if ($result->code == 0) {
                    return ResponeSuccess('下分成功', 0);
                }
                return ResponeFails('下分失败', $result->code);
            }
        } catch (ConnectException $e){
            \Log::error('[平台下分][连接超时]'.$e);
            return ResponeFails('请求失败');
        } catch (NewException $e){
            \Log::error('[平台下分]'.$e);
            return ResponeFails($e->getMessage());
        }
    }

}

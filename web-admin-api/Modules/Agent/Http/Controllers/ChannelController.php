<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Requests\SelectGameIdRequest;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AccountsSet;
use Models\AdminPlatform\SystemLog;
use Models\Agent\ChannelInfo;
use Models\Record\RecordUserLogon;
use Models\Treasure\RecordScoreDaily;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\ChannelUserRelation;
use Modules\Agent\Http\Requests\AddChannelRequest;
use Transformers\AccountsInfoTransformer;
use Transformers\ChannelInfoTransformer;
use Validator;

class ChannelController extends BaseController
{
    /**
     * 渠道列表（计算的是渠道直推用户的）
     *
     * @return Response
     */
    public function channel_info()
    {
        Validator::make(request()->all(), [
            'channel_id' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'channel_id.numeric' => '渠道ID必须是数字，请重新输入！',
        ])->validate();
        //查询所有一级渠道列表
        $list = ChannelInfo::where('parent_id', 0)
            ->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
            ->andFilterWhere('channel_id', request('channel_id'))
            ->paginate(\request('pagesize',config('page.list_rows')));
        //获取该分页下的channel_id
        $channel_ids = $list->pluck('channel_id');
        list($channel, $people_sum, $pay, $withdrawal, $people_bind_sum) = $this->statisc_channel($channel_ids);
        list($sub_channel, $sub_people_sum, $sub_pay, $sub_withdrawal, $sub_people_bind_sum) = $this->statisc_sub_channel($channel_ids);

        foreach ($list as $k => $v) {
            $list[$k]['people_sum']           = ($people_sum[$v->channel_id]->total ?? 0) + validate_data($sub_people_sum[$v->channel_id]->total ?? 0);//总推广人数
            $list[$k]['directly_under_users'] = $people_sum[$v->channel_id]->total ?? 0;//直属推广玩家人数
            $list[$k]['main_winlose_sum']     = realCoins($channel[$v->channel_id]->ChangeScore ?? 0);//直属推广系统平台盈利统计
            $list[$k]['sub_winlose_sum']      = realCoins(validate_data($sub_channel[$v->channel_id]->ChangeScore ?? 0));//子渠道推广系统平台盈利统计
            $list[$k]['winlose_sum']          = $list[$k]['main_winlose_sum'] + $list[$k]['sub_winlose_sum'];
            $list[$k]['main_bet_sum']         = realCoins($channel[$v->channel_id]->JettonScore ?? 0);//直属推广总下注统计
            $list[$k]['sub_bet_sum']          = realCoins(validate_data($sub_channel[$v->channel_id]->JettonScore ?? 0));//子渠道推广总下注统计
            $list[$k]['bet_sum']              = $list[$k]['main_bet_sum'] + $list[$k]['sub_bet_sum'];
            // $list[$k]['main_stream_score']    = realCoins($channel[$v->channel_id]->StreamScore ?? 0);//直属推广总流水统计
            $list[$k]['sub_stream_score']     = realCoins(validate_data($sub_channel[$v->channel_id]->StreamScore ?? 0));//子渠道推广总流水统计
            $list[$k]['stream_score']         = $list[$k]['main_stream_score'] + $list[$k]['sub_stream_score'];
            // $list[$k]['balance']              = realCoins($v->balance) ?? 0; //佣金余额
            $list[$k]['pay_sum']              = ($pay[$v->channel_id]->amount ?? 0) + validate_data($sub_pay[$v->channel_id]->amount ?? 0);//直属推广充值统计
            $list[$k]['withdraw_sum']         = ($withdrawal[$v->channel_id]->money ?? 0) + validate_data($sub_withdrawal[$v->channel_id]->money ?? 0);//直属推广统计
            $list[$k]['recharge_sum']         = ($pay[$v->channel_id]->total ?? 0) + validate_data($sub_pay[$v->channel_id]->total ?? 0);//直属推广充值人数统计
            $list[$k]['main_bind_mobile_sum'] = validate_data($people_bind_sum[$v->channel_id]->total ?? 0);//绑定手机人数
            $list[$k]['sub_bind_mobile_sum']  = validate_data($sub_people_bind_sum[$v->channel_id]->total ?? 0);//绑定手机人数
            $list[$k]['bind_mobile_sum']      = $list[$k]['main_bind_mobile_sum'] + $list[$k]['sub_bind_mobile_sum'];//绑定手机人数
            $list[$k]['spread_domain']        = $v['channel_domain'] . '/?channelid=' . $v['channel_id']; //推广域名

            $arr = ChannelInfo::FORBID_GIFTS;
            foreach ($arr as $key => $val){
                $arr[$key] = intval(($val & $v['forbid_value']) > 0);
            }
            $list[$k]['forbid_value']         = $v['forbid_value'];
            $list[$k]['forbid_gifts']         = $arr;
        }
        return $this->response->paginator($list, new ChannelInfoTransformer());
    }

    private function statisc_channel($channel_ids)
    {
        //直推玩家输赢，投注，流水
        $channel = $this->channel_sql($channel_ids, [
            //\DB::raw('SUM(b.ChangeScore) as ChangeScore'),
            \DB::raw('SUM(b.JettonScore-b.RewardScore) as ChangeScore'),  //输赢（损益）改为：平台盈利=投注-中奖
            \DB::raw('SUM(b.JettonScore) as JettonScore'),  //投注
            \DB::raw('SUM(b.StreamScore) as StreamScore')   //流水
        ], [RecordScoreDaily::tableName() . ' as b', 'b.UserID', '=', 'a.user_id']);
        //直属推广充值统计
        $pay = $this->channel_sql($channel_ids, [
            \DB::raw('SUM(b.amount) as amount'),
            \DB::raw('COUNT(DISTINCT b.user_id) as total')
        ], [PaymentOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'], ['b.payment_status', PaymentOrder::SUCCESS, null]);
        //直属推广统计
        $withdrawal = $this->channel_sql($channel_ids, [
            \DB::raw('SUM(b.money) as money'),
            \DB::raw('COUNT(DISTINCT B.user_id) as total')
        ], [WithdrawalOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'], ['b.status', WithdrawalOrder::PAY_SUCCESS, null]);
        //直属推广玩家人数
        $people_sum = $this->channel_sql($channel_ids, [
            \DB::raw('COUNT(DISTINCT user_id) as total')
        ], [AccountsInfo::tableName() . ' as b', 'a.user_id', '=', 'b.UserID']);
        //直属绑定手机号玩家
        $people_bind_sum = $this->channel_sql($channel_ids, [
            \DB::raw('COUNT(DISTINCT user_id) as total')
        ], [AccountsInfo::tableName() . ' as b', 'a.user_id', '=', 'b.UserID'], ['b.RegisterMobile', '', '<>']);
        return [$channel, $people_sum, $pay, $withdrawal, $people_bind_sum];
    }

    private function statisc_sub_channel($channel_ids)
    {
        //子渠道推广玩家输赢，投注，流水
        $channel = $this->sub_channel_sql($channel_ids,
            [
                //\DB::raw('SUM(b.ChangeScore) as ChangeScore'),
                \DB::raw('SUM(b.JettonScore-b.RewardScore) as ChangeScore'),  //输赢（损益）改为：平台盈利=投注-中奖
                \DB::raw('SUM(b.JettonScore) as JettonScore'),  //投注
                \DB::raw('SUM(b.StreamScore) as StreamScore')   //流水
            ],
            [RecordScoreDaily::tableName() . ' as b', 'b.UserID', '=', 'a.user_id'], '', [
                \DB::raw('SUM(d.ChangeScore) as ChangeScore'),//输赢（损益）改为：平台盈利=投注-中奖
                \DB::raw('SUM(d.JettonScore) as JettonScore'),  //投注
                \DB::raw('SUM(d.StreamScore) as StreamScore')   //流水
            ]);
        //子渠道推广充值统计
        $pay = $this->sub_channel_sql($channel_ids, [
            \DB::raw('SUM(b.amount) as amount'),
            \DB::raw('COUNT(DISTINCT b.user_id) as total')
        ], [PaymentOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'], ['b.payment_status', PaymentOrder::SUCCESS, null], [
            \DB::raw('SUM(d.amount) as amount'),
            \DB::raw('SUM(d.total) as total')
        ]);
        //子渠道推广统计
        $withdrawal = $this->sub_channel_sql($channel_ids, [
            \DB::raw('SUM(b.money) as money'),
            \DB::raw('COUNT(DISTINCT B.user_id) as total')
        ], [WithdrawalOrder::tableName() . ' as b', 'a.user_id', '=', 'b.user_id'], ['b.status', WithdrawalOrder::PAY_SUCCESS, null], [
            \DB::raw('SUM(d.money) as money'),
            \DB::raw('SUM(d.total) as total')
        ]);
        //子渠道推广玩家人数
        $people_sum      = $this->sub_channel_sql($channel_ids, [
            \DB::raw('COUNT(DISTINCT user_id) as total')
        ], null, null, [
            \DB::raw('SUM(d.total) as total')
        ]);
        $people_bind_sum = $this->sub_channel_sql($channel_ids, [
            \DB::raw('COUNT(DISTINCT user_id) as total')
        ], [AccountsInfo::tableName() . ' as b', 'a.user_id', '=', 'b.UserID'], ['b.RegisterMobile', '', '<>'], [
            \DB::raw('SUM(d.total) as total')
        ]);
        return [
            $channel->keyBy('parent_id'),
            $people_sum->keyBy('parent_id'),
            $pay->keyBy('parent_id'),
            $withdrawal->keyBy('parent_id'),
            $people_bind_sum->keyBy('parent_id')];
    }

    private function channel_sql($channel_ids, $select, $join = null, $where = null)
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
        $result = $result->whereIn('a.channel_id', $channel_ids)
            ->groupBy('a.channel_id')
            ->get()->keyBy('channel_id');
        return $result;
    }

    private function sub_channel_sql($channel_ids, $select, $join = null, $where = null, $mainSelect = null)
    {

        list($table, $first, $operator, $second) = $join;
        $select  = array_merge($select, ['a.channel_id']);
        $sub_sql = \DB::table(ChannelUserRelation::tableName() . ' as a')->select($select);
        if ($join) {
            $sub_sql->leftJoin($table, $first, $operator, $second);
        }
        if ($where) {
            list($column, $value, $operator) = $where;
            $sub_sql->where($column, $operator ?? '=', $value);
        }
        $sub_sql->groupBy('a.channel_id')
            ->toSql();
        if ($mainSelect) {
            $main_select = array_merge($mainSelect, ['c.parent_id']);
        } else {
            $main_select = ['c.parent_id', 'd.*'];
        }
        $result = \DB::table(ChannelInfo::tableName() . ' AS c')
            ->select($main_select)
            ->leftJoinSub($sub_sql, 'd', 'c.channel_id ', 'd.channel_id')
            ->whereIn('c.parent_id', $channel_ids)
            ->groupBy('c.parent_id')
            ->get();
        return $result;
    }

    /**
     * 查看子渠道列表
     *
     * @return Response
     */
    public function sub_channel_info()
    {

        Validator::make(request()->all(), [
            'sub_channel_id' => ['nullable', 'numeric'],
            'channel_id' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'sub_channel_id.numeric' => '渠道ID必须是数字，请重新输入！',
        ])->validate();

        //查询所有一级渠道列表
        $list = ChannelInfo::andFilterWhere('parent_id', request('channel_id'))
            ->andFilterBetweenWhere('created_at', request('start_date'), request('end_date'))
            ->andFilterWhere('channel_id', request('sub_channel_id'))
            ->paginate(config('page.list_rows'));
        //获取该分页下的channel_id
        $channel_ids = $list->pluck('channel_id');
        list($channel, $people_sum, $pay, $withdrawal, $people_bind_sum) = $this->statisc_channel($channel_ids);
        foreach ($list as $k => $v) {
            $list[$k]['winlose_sum']   = (realCoins($channel[$v->channel_id]->ChangeScore ?? 0));//渠道系统总输赢改为：平台盈利=投注-中奖
            $list[$k]['bet_sum']       = realCoins($channel[$v->channel_id]->JettonScore ?? 0);//渠道总下注
            $list[$k]['stream_score']  = realCoins($channel[$v->channel_id]->StreamScore ?? 0);//渠道总流水
            $list[$k]['balance']       = realCoins($v->balance); //佣金余额
            $list[$k]['people_sum']    = $people_sum[$v->channel_id]->total ?? 0;//统计总推广人数
            $list[$k]['pay_sum']       = $pay[$v->channel_id]->amount ?? 0;//统计总充值
            $list[$k]['withdraw_sum']  = $withdrawal[$v->channel_id]->money ?? 0;
            $list[$k]['recharge_sum']  = $pay[$v->channel_id]->total ?? 0;//统计总充值人数
            $list[$k]['spread_domain'] = $v['channel_domain'] . '?channelid=' . $v['channel_id']; //推广域名

            $arr = ChannelInfo::FORBID_GIFTS;
            foreach ($arr as $key => $val){
                $arr[$key] = intval(($val & $v['forbid_value']) > 0);
            }
            $list[$k]['forbid_value']         = $v['forbid_value'];
            $list[$k]['forbid_gifts']         = $arr;

        }
        return $this->response->paginator($list, new ChannelInfoTransformer());
    }

    /**
     * 添加渠道
     *
     * @return Response
     */
    public function add_channel(AddChannelRequest $request)
    {
        //生成渠道账号信息
        $request_params                  = new ChannelInfo();//渠道账号表
        $request_params->password        = Hash::make($request->input('password'));
        $request_params->nickname        = $request->input('nickname');
        $request_params->phone           = $request->input('phone');
        $request_params->contact_address = $request->input('contact_address');
        // $request_params->return_rate     = $request->input('return_rate');
        // $request_params->return_type     = $request->input('return_type');
        $request_params->remarks         = $request->input('remarks');
        $request_params->admin_id        = $this->admin_id();  //后台登录管理员id
        $request_params->channel_domain  = $request->input('channel_domain');

        //禁止礼金
        $str = request('bind_give',0) . request('reg_give',0);
        $request_params->forbid_value = base_convert($str,2,10);

        if ($request_params->save()) {
            SystemLog::addLogs('添加渠道，id为：' . $request_params->channel_id);
            return ResponeSuccess('操作成功');
        }
        return ResponeFails('操作失败');
    }

    /**
     * 编辑渠道
     *
     * @return Response
     */
    public function edit_channel(AddChannelRequest $request)
    {
        $channel_id = $request->input('channel_id', '');
        if (!$channel_id) {
            return ResponeFails('没有渠道id');
        }
        $model = ChannelInfo::find($channel_id);//渠道主表
        if (!$model) {
            return ResponeFails('没有找到渠道');
        }
        $model->loadFromRequest();
        if ($request->input('password')) {
            $model->password = Hash::make($request->input('password'));
        } else {
            unset($model->password);
        }

        //禁止礼金
        $str = request('bind_give',0) . request('reg_give',0);
        $model->forbid_value = base_convert($str,2,10);

        if ($model->save()) {
            SystemLog::addLogs('编辑渠道，id为：' . $channel_id);
            return ResponeSuccess('操作成功');
        }
        return ResponeFails('操作失败');
    }

    /**
     * 渠道禁用/启用（修改渠道的状态）
     *
     */
    public function channel_status()
    {
        $channel_id    = request('channel_id');
        $status        = request('status') ? 0 : 1;
        $channel_ids   = ChannelInfo::where('parent_id', $channel_id)->pluck('channel_id');
        $channel_ids[] = (int)$channel_id;
        \DB::beginTransaction();
        try {
            $res = ChannelInfo::whereIn('channel_id', $channel_ids)->update(['nullity' => $status]);
            if ($res == count($channel_ids)) {
                \DB::commit();
                SystemLog::addLogs('修改渠道的状态，id为：' . $channel_id);
                return ResponeSuccess('操作成功');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return ResponeFails('操作失败');
        }
    }

    /*
     * 渠道推广的玩家信息
     * */
    public function channel_user_list(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'channel_id'  => ['required'],
            'platform_id' => ['nullable', 'numeric'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date'],
        ])->validate();

        $channel_id = $request->input('channel_id');
        if (!$channel_id) {
            return ResponeFails('渠道id必传');
        }
        $model = ChannelInfo::find($channel_id);
        if (!$model) {
            return ResponeFails('渠道不存在');
        }
        $list        = AccountsInfo::from(AccountsInfo::tableName() . ' as a')
            ->select('a.*','b.user_id','b.channel_id','c.user_id as c_user_id')
            ->where('a.IsAndroid', 0)
            ->andFilterWhere('a.GameID', intval(request('game_id')))
            ->andFilterWhere('a.NickName', request('nickname'))
            ->andFilterWhere('a.PlatformID', request('platform_id'))
            ->andFilterBetweenWhere('a.RegisterDate', request('start_date'), request('end_date'))
            ->leftJoin(ChannelUserRelation::tableName() . ' as b', function ($join) {
                $join->on('a.UserID', '=', 'b.user_id');
            })->leftJoin(AccountsSet::tableName() . ' as c', function ($join) {
                $join->on('a.UserID', '=', 'c.user_id');
            })
            ->where('b.channel_id', $channel_id);
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list        = $this->searchStatus($list, request('status_type'))->orderBy('a.RegisterDate', 'desc')->paginate($page_list_rows);
        $user_ids    = $list->pluck('UserID');
        $pays        = PaymentOrder::select([
            'user_id',
            \DB::raw('SUM(coins) as amount')
        ])->whereIn('user_id', $user_ids)->where('payment_status', PaymentOrder::SUCCESS)->groupBy('user_id')->get()->keyBy('user_id');
        $withdrawals = WithdrawalOrder::select([
            'user_id',
            \DB::raw('SUM(real_gold_coins) as money')
        ])->whereIn('user_id', $user_ids)->where('status', WithdrawalOrder::PAY_SUCCESS)->groupBy('user_id')->get()->keyBy('user_id');
        $waters      = RecordScoreDaily::select([
            'UserID',
            \DB::raw('SUM(StreamScore) as StreamScore'),
            \DB::raw('SUM(RewardScore-JettonScore) as win_lose')
        ])->whereIn('UserID', $user_ids)->groupBy('UserID')->get()->keyBy('UserID');

        foreach ($list as $k => $v) {
            $list[$k]['pay']          = $pays[$v->user_id]->amount ?? 0;//充值
            $list[$k]['withdraw']     = $withdrawals[$v->user_id]->money ?? 0;
            $list[$k]['spring_water'] = $waters[$v->user_id]->StreamScore ?? 0;//流水
            $list[$k]['user_win_lose']= $waters[$v->user_id]->win_lose ?? 0;//玩家输赢=中奖-投注
        }
        return $this->response->paginator($list, new AccountsInfoTransformer());
    }

    /**
     * 获取用户充值
     *
     * @param int $user_id 用户id
     */
    protected function getPaySum($user_id, $is_today = false)
    {
        $obj = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS);
        if ($is_today === true) {
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->sum('coins');
    }

    /**
     *
     *
     * @param int $user_id 用户id
     */
    protected function getWithdrawSum($user_id, $is_today = false)
    {
        $obj = WithdrawalOrder::where('user_id', $user_id)->where('status', WithdrawalOrder::PAY_SUCCESS);
        if ($is_today === true) {
            $obj->whereDate('created_at', date("Y-m-d"));
        }
        return $obj->sum('real_gold_coins');
    }

    /**
     * 统计用户流水
     *
     * @param int $user_id 用户id
     */
    protected function getUserCount($user_id, $is_today = false)
    {
        $obj = RecordScoreDaily::where('UserID', $user_id);
        if ($is_today === true) {
            $obj->whereDate('UpdateDate', date("Y-m-d"));
        }
        return $obj->sum('StreamScore');
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
                        $query->where('c.nullity', AccountsSet::NULLITY_ON)
                            ->where('c.withdraw', AccountsSet::WITHDRAW_ON);
                    });
                    $query->orWhere(function ($query) {
                        $query->whereNull('c.nullity')
                            ->whereNull('c.withdraw');
                    });
                });
                break;
            //禁止登录
            case 2:
                $obj->where(function ($query) {
                    $query->where('c.nullity', AccountsSet::NULLITY_OFF);
                });
                break;

            case 3:
                $obj->where(function ($query) {
                    $query->where('c.withdraw', AccountsSet::WITHDRAW_OFF);
                });
                break;
            //全部禁止
            case 4:
                $obj->where(function ($query) {
                    $query->orWhere(function ($query) {
                        $query->where('c.nullity', AccountsSet::NULLITY_OFF)
                            ->where('c.withdraw', AccountsSet::WITHDRAW_OFF);
                    });
                });
                break;
        }
        return $obj;
    }

    /**
     * 后台账户给一级渠道添加子渠道
     *
     * @return Response
     */
    public function channel_add_subset(AddChannelRequest $request)
    {
        Validator::make(request()->all(), [
            'parent_id' => ['required'],
        ], [
            'parent_id.required' => '上级渠道ID必填！',
        ])->validate();
        $request_params                  = new ChannelInfo();//渠道主表
        $request_params->password        = Hash::make($request->input('password'));
        $request_params->nickname        = $request->input('nickname');
        $request_params->phone           = $request->input('phone');
        $request_params->contact_address = $request->input('contact_address');
        //$request_params->return_rate     = $request->input('return_rate');
        //$request_params->return_type     = $request->input('return_type');
        $request_params->remarks         = $request->input('remarks');
        $request_params->channel_domain  = $request->input('channel_domain');
        $request_params->admin_id        = $this->admin_id();  //后台登录管理员id
        $request_params->parent_id       = $request->input('parent_id');//上级渠道id
        //return $request_params;
        //禁止礼金
        $str = request('bind_give',0) . request('reg_give',0);
        $request_params->forbid_value = base_convert($str,2,10);
        if ($request_params->save()) {
            return ResponeSuccess('操作成功');
        } else {
            return ResponeFails('操作失败');
        }
    }

    /**
     * 后台账户给一级渠道编辑子渠道
     *
     * @return Response
     */
    public function channel_edit_subset(AddChannelRequest $request)
    {
        $id = $request->input('channel_id', '');
        if (!$id) {
            return ResponeFails('没有渠道id');
        }
        $model = ChannelInfo::find($id);//渠道主表
        if (!$model) {
            return ResponeFails('没有找到渠道');
        }
        $model->loadFromRequest();
        if ($request->input('password')) {
            $model->password = Hash::make($request->input('password'));
        } else {
            unset($model->password);
        }
        //禁止礼金
        $str = request('bind_give',0) . request('reg_give',0);
        $model->forbid_value = base_convert($str,2,10);

        if ($model->save()) {
            return ResponeSuccess('操作成功');
        }
        return ResponeFails('操作失败');
    }
    /**
     * 渠道状态设置（修改则改变整条线的）
     *
     */
    /* public function status()
    {
        $id = request('id');
        $nullity = request('nullity');
        $AccountInfo = new ChannelInfo();
        $list = $AccountInfo->getIdOrPid(2);//获取所用代理
        $ids = getTreeRegroup($list, $id);//获取递归重组后的数据
        DB::beginTransaction();
        try {
            $res = $AccountInfo->whereIn('id', $ids)->update(['nullity' => $nullity]);
            if ($res == count($ids)) {
                DB::commit();
                return ResponeSuccess('操作成功');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response->errorInternal('操作失败');
        }
    }
    /**
     * 渠道删除（修改则改变整条线的）
     *
     */
    /* public function del()
    {
        $id = request('id');
        $AccountInfo = new ChannelInfo();
        $list = $AccountInfo->getIdOrPid(2);//获取所用渠道
        $ids = getTreeRegroup($list, $id);//获取递归重组后的数据
        DB::beginTransaction();
        try {
            $res = $AccountInfo->whereIn('id', $ids)->delete();
            if ($res == count($ids)) {
                DB::commit();
                return ResponeSuccess('删除成功');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response->errorInternal('删除失败');
        }
     }*/

    public function import_channel_subset(Request $request){
        $parent_id = $request->input('parent_id', '');
        if (!$parent_id) {
            return ResponeFails('没有父渠道id');
        }
        $parent = ChannelInfo::find($parent_id);
        if(!$parent){
            return ResponeFails('该父渠道不存在');
        }
        $admin_id = \Auth::guard('admin')->id();
        $excel_file_path = $request->file('import');//接受文件路径
        $datas = Excel::toArray(new ChannelInfo(), $excel_file_path);
        $list = $datas[0] ?? [];
        if(count($list) < 2) {
            return ResponeFails('导入缺少数据');
        }
        unset($list[0]);
        if(count($list) > 100){
            return ResponeFails('导入数据不能超过100条');
        }
        $phone_column = array_column($list,3);
        if (count($phone_column) != count(array_unique($phone_column))) {
            return ResponeFails('联系电话有重复值');
        }
        //查询重复手机号
        $phone = ChannelInfo::whereIn('phone',$phone_column)->pluck('phone')->toArray();
        if(!empty($phone)){
            return ResponeFails('联系电话:'.$phone[0].'已存在');
        }
        $data = [];
        foreach($list as $k => $item){
            if(count($item) < 6)
                return ResponeFails('编号:'.$item[0].',该行缺少数据');

            if(!$item[1])
                return ResponeFails('编号:'.$item[0].',该行缺少真实姓名');

            if(!$item[2])
                return ResponeFails('编号:'.$item[0].',该行缺少密码');

            if(!$item[3] || !preg_match('/^1\d{10}$/', $item[3]))
                return ResponeFails('编号:'.$item[0].',该行联系电话不正确');

            if(!$item[4] || !is_numeric($item[4]) || $item[4]<0)
                return ResponeFails('编号:'.$item[0].',该行返利比例不正确');

            if($item[4] >= $parent->return_rate)
                return ResponeFails('编号:'.$item[0].',返利比例不能大于等于父级比例');

            if(!$item[5])
                return ResponeFails('编号:'.$item[0].',该行缺少推广域名');

            $data[] = [
                'admin_id'       => $admin_id,
                'parent_id'      => $parent_id,
                'password'       => Hash::make($item[2]),
                'nickname'       => $item[1],
                'phone'          => $item[3],
                'return_rate'    => $item[4],
                'channel_domain' => $item[5],
                'return_type'    => $parent->return_type,
                'remarks'        => $item[1],
                'contact_address'=> $item[1],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ];
        }
        try{
            $res = DB::table('AgentDB.dbo.channel_info')->insert($data);
            if(!$res){
                return ResponeFails('插入失败');
            }
            return ResponeSuccess('操作成功');
        }catch (\Exception $e){
            return ResponeFails('插入失败:'.$e->getMessage());
        }
    }
    /**
     * 官方渠道统计
     *
     */
    public function official_channel(){
        //总人数
        $user_ids = AccountsInfo::where('IsAndroid',0)->count('UserID');
        //渠道人数
        $channel_ids = ChannelUserRelation::select('user_id')->count('user_id');
        //官方人数
        $res=[];
        $res['official_people_sum'] =  $user_ids-$channel_ids;
        //充值
        $user_recharge = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select( \DB::raw('SUM(b.amount) as amount'),
                      \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->first();
        $channel_recharge = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.user_id','=','b.user_id')
            ->select( \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->first();
        $res['official_recharge_score'] = $user_recharge['amount'] - $channel_recharge['amount'];
        $res['official_recharge_total'] = $user_recharge['total'] - $channel_recharge['total'];
        //提现
        $user_recharge = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select( \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('a.IsAndroid',0)
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->first();
        $channel_recharge = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(PaymentOrder::tableName().' as b','a.user_id','=','b.user_id')
            ->select( \DB::raw('SUM(b.amount) as amount'),
                \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('b.payment_status',PaymentOrder::SUCCESS)
            ->first();
        $res['official_recharge_score'] = $user_recharge['amount'] - $channel_recharge['amount'];
        $res['official_recharge_total'] = $user_recharge['total'] - $channel_recharge['total'];
        $user_withdraw = AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(WithdrawalOrder::tableName().' as b','a.UserID','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.money) as withdraw'),
                \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('a.IsAndroid',0)
            ->where('b.status', WithdrawalOrder::PAY_SUCCESS)
            ->first();
        $channel_withdraw = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(WithdrawalOrder::tableName().' as b','a.user_id','=','b.user_id')
            ->select(
                \DB::raw('SUM(b.money) as withdraw'),
                \DB::raw('COUNT(DISTINCT b.user_id) as total'))
            ->where('b.status', WithdrawalOrder::PAY_SUCCESS)
            ->first();
        $res['official_withdrawal_score'] = $user_withdraw['withdraw'] - $channel_withdraw['withdraw'];
        $res['official_withdrawal_total'] = $user_withdraw['total'] - $channel_withdraw['total'];
        //有效投注
        $user_jetton_score =AccountsInfo::from(AccountsInfo::tableName().' as a')
            ->leftJoin(RecordScoreDaily::tableName().' as b','a.UserID','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.JettonScore-b.RewardScore) as ChangeScore'),  //输赢（损益）改为：平台盈利=投注-中奖
                \DB::raw('SUM(b.JettonScore) as JettonScore')  //投注
            )->first();
        $channel_jetton_score =ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(RecordScoreDaily::tableName().' as b','a.user_id','=','b.UserID')
            ->select(
                \DB::raw('SUM(b.JettonScore-b.RewardScore) as ChangeScore'),  //输赢（损益）改为：平台盈利=投注-中奖
                \DB::raw('SUM(b.JettonScore) as JettonScore')  //投注
            )->first();
        $res['official_profit'] = realCoins($user_jetton_score['ChangeScore'] - $channel_jetton_score['ChangeScore'] ?? 0);
        $res['official_jetton_score'] = realCoins($user_jetton_score['JettonScore'] - $channel_jetton_score['JettonScore'] ?? 0);
        //绑定手机号人数
        $user_bind_mobile = AccountsInfo::where('IsAndroid',0)->where('RegisterMobile','<>','')->count('UserID');
        $channel_bind_mobile = ChannelUserRelation::from(ChannelUserRelation::tableName().' as a')
            ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
            ->select(
                \DB::raw('COUNT(a.user_id) as total')
            )
            ->where('b.RegisterMobile','<>','')
            ->first();
        $res['official_bind_mobile'] = $user_bind_mobile - $channel_bind_mobile['total'];
        return ResponeSuccess('查询成功', $res);
    }
}

<?php
/**
 * 充值
 */

namespace Modules\Order\Http\Controllers;

use App\Http\Requests\SelectGameIdRequest;
use App\Rules\CheckVip;
use App\Rules\UserGameExist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\VipBusinessman;
use Transformers\PaymentOrderTransformer;
use Validator;


class PaymentOrderController extends Controller
{
    /**
     * 充值首页列表
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(SelectGameIdRequest $request)
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
        $data              = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->andFilterWhere('payment_status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->andFilterWhere('order_no', $request->input('order_no'))
            ->andFilterWhere('third_order_no', $request->input('third_order_no'))
            ->orderBy($search_time, 'DESC');
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = $request->input('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = realCoins($statistics_coins->sum('coins'));
        return $this->response->paginator($list, new PaymentOrderTransformer())->addMeta('count', $count)->addMeta('status', PaymentOrder::STATUS);
    }

    /**
     * 内部充值首页列表
     *
     * @param Request $request
     *
     * @return Response
     */
    public function official(SelectGameIdRequest $request)
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
        $data              = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
            ->whereIn('payment_provider_id', array_values(PaymentOrder::OFFICIAL_KEYS))
            ->andFilterWhere('payment_status', $request->input('status'))
            ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
            ->andFilterWhere('order_no', $request->input('order_no'))
            ->orderBy($search_time, 'DESC');
        $statistics_people = clone $data;
        $statistics_coins  = clone $data;
        // 新增需求，自定义每页条数
        $page_list_rows = $request->input('page_sizes') ?? config('page.list_rows');
        $list              = $data->paginate($page_list_rows);
        $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
        $count['moneys']   = realCoins($statistics_coins->sum('coins'));
        return $this->response->paginator($list, new PaymentOrderTransformer())->addMeta('count', $count)->addMeta('status', PaymentOrder::STATUS);
    }

    //官方添加金币
    public function officialStatus($status)
    {
        if (!in_array($status, ['pass', 'fails'])) {
            return ResponeFails('请求参数不正确');
        }
        Validator::make(request()->all(), [
            'id' => ['required'],
        ], [
            'id.required' => '参数不全',
        ])->validate();
        $model = PaymentOrder::where('id', request('id'))
            ->where('payment_provider_id', '<=', 0)
            ->where('payment_status', PaymentOrder::WAIT)->first();
        if ($model) {
            if ($status == 'pass' && $model->officialAddCoins()) {
                return ResponeSuccess('添加金币成功');
            }
            if ($status == 'fails' && $model->orderFails()) {
                return ResponeSuccess('修改订单状态成功');
            }
            return ResponeFails('修改订单失败');
        }
        return ResponeFails('订单不存在');
    }

    //vip商人订单列表
    public function vip(SelectGameIdRequest $request)
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
        $admin       = $this->user()->id;
        $list        = VipBusinessman::where('admin_id', $admin)->where('nullity', VipBusinessman::NULLITY_ON)->get();
        if (count($list) > 0) {
            $ids               = collect($list)->pluck('id');
            $data              = PaymentOrder::andFilterWhere('game_id', $request->input('game_id'))
                ->andFilterWhere('payment_status', $request->input('status'))
                ->andFilterBetweenWhere($search_time, $request->input('start_time'), $request->input('end_time'))
                ->whereIn('payment_provider_id', $ids)
                ->where('payment_type', VipBusinessman::SIGN)
                ->andFilterWhere('order_no', $request->input('order_no'))
                ->andFilterWhere('third_order_no', $request->input('third_order_no'))
                ->orderBy($search_time, 'DESC');
            // 新增需求，自定义每页条数
            $page_list_rows = $request->input('page_sizes') ?? config('page.list_rows');
            $orders            = $data->paginate($page_list_rows);
            $statistics_people = clone $data;
            $statistics_coins  = clone $data;
            $count['peoples']  = $statistics_people->distinct('user_id')->count('user_id');
            $count['moneys']   = realCoins($statistics_coins->sum('coins'));
            return $this->response->paginator($orders, new PaymentOrderTransformer())
                ->addMeta('vip_businessman', $list)->addMeta('count', $count)->addMeta('status', PaymentOrder::STATUS);
        }
        return ResponeFails('当前账号没有绑定vip商人，如需操作，请先绑定');
    }

    public function vipPay(Request $request)
    {
        $admin = $this->user()->id;
        $model = new VipBusinessman();
        Validator::make($request->all(), [
            'money'   => ['required','numeric','max:999999'],
            'game_id' => ['required', new UserGameExist()],
            'vip_id'  => ['required',
                          Rule::exists($model->getTable(), 'id')->where('admin_id', $admin)->where('nullity', VipBusinessman::NULLITY_ON),
                          new CheckVip(moneyToCoins($request->input('money')))
            ],
        ], [
            'money.required'   => '充值'.config('set.amount').'不能为空',
            'money.numeric'    => '充值'.config('set.amount').'为数值类型',
            'money.max'        => '充值'.config('set.amount').'最大值为999999',
            'game_id.required' => '玩家ID不能为空',
            'vip_id.required'  => 'VIP商人ID不能为空',
            'vip_id.exists'    => 'VIP商人ID不存在或您不是绑定者',
        ])->validate();
        $user  = AccountsInfo::where('GameID', $request->input('game_id'))->first();
        $order = new PaymentOrder();
        if ($order->saveVipOrder($request->input('money'), $user->UserID, $request->input('vip_id'))) {
            if ($order->vipAddCoins()) {
                return ResponeSuccess('充值成功');
            }
            return ResponeFails('充值失败:余额不足');
        }
        return ResponeFails('充值失败');
    }

    //补单操作
    public function compensateOrder(Request $request){
        Validator::make($request->all(), [
            'money'    => ['required','numeric'],
            'order_id' => ['required','numeric'],
            'remarks'  => ['required']
        ], [
            'money.required'   => '补单'.config('set.amount').'不能为空',
            'money.numeric'    => '补单'.config('set.amount').'为数字',
            'order_id.required'=> '订单ID不能为空',
            'order_id.numeric' => '订单ID为数字',
            'remarks.required' => '备注内容不能为空',
        ])->validate();
        $order = PaymentOrder::find(request('order_id'));
        if(!$order){
            return ResponeFails('该订单不存在');
        }
        if($order->payment_status == PaymentOrder::SUCCESS){
            return ResponeFails('该订单状态无法操作');
        }
        if($order->relation_order_no){
            return ResponeFails('补单订单无法操作');
        }
        $compensateOrder = new PaymentOrder();
        if ($compensateOrder->saveCompensateOrder($order)) {
            if (in_array($order->payment_provider_id,PaymentOrder::OFFICIAL_KEYS)) {
                //官方加金币
                $bool = $compensateOrder->officialAddCoins();
            }else{
                //四方加金币
                $bool = $compensateOrder->thirdAddCoins();
            }
            if ($bool) {
                $order->payment_status = PaymentOrder::FAILS;
                $order->remarks .= $order->remarks ? PHP_EOL.request('remarks') : request('remarks');
                $order->save();
                return ResponeSuccess('补单成功');
            }
            return ResponeFails('补单失败');
        }
        return ResponeFails('补单失败');
    }
}

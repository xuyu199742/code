<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\MsgPush;
use App\Rules\UserExist;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Treasure\GameScoreInfo;
use Modules\Client\Http\Requests\WithdrawalOrdersRequest;
use Modules\Client\Http\Requests\WithdrawalRequest;
use Modules\Client\Transformers\PaymentOrderResource;
use Modules\Client\Transformers\WithdrawalOrderResource;
use Validator;

class OrderController extends Controller
{

    /**
     * 下单
     * @param WithdrawalRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function withdrawal(WithdrawalRequest $request)
    {
        if(!$this->checkUserLevelConfig(request('user_id'), UserLevel::WITHDRAWAL)) {
            return ResponeFails('对方目前VIP等级无法使用该功能');
        }
        //外接平台在线不允许操作
        $GameScoreInfo = GameScoreInfo::where('UserID',\request('user_id'))->first();
        if ($GameScoreInfo->CurPlatformID != 0){
            return ResponeFails('账号处于游戏状态，请手动刷新金币');
        }
        $withdrawal = new WithdrawalOrder();
        $withdrawal->fill($request->all());
        if ($withdrawal->initOrder()) {
            //订单消息后台推送
            MsgPush::dispatch(['type' => 'withdraw']);
            return ResponeSuccess(config('set.withdrawal').'成功,等待收款');
        }
        return ResponeFails(config('set.withdrawal').'失败');
    }

    /**
     * 订单列表
     * @param WithdrawalOrdersRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function withdrawalList(WithdrawalOrdersRequest $request)
    {
        $list = WithdrawalOrder::where('user_id', $request->input('user_id'))
            ->orderBy('created_at', 'desc');
        return ResponeSuccess(
            '查询成功',
            WithdrawalOrderResource::collection($list->paginate())
        );

    }

    /**
     * 表单数据
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function withdrawAddress(Request $request)
    {
        $address = WithdrawalOrder::where('user_id', $request->input('user_id'))
            ->orderBy('created_at', 'desc')->first();
        return ResponeSuccess(
            '查询成功',
            $address?new WithdrawalOrderResource($address):[]
        );
    }

    //充值订单列表
    public function payList(Request $request){
        Validator::make(request()->all(), [
            'user_id' => ['required', new UserExist('none')],
        ], [
            'user_id.required' => '参数不全',
        ])->validate();
        $list = PaymentOrder::where('user_id', $request->input('user_id'))
            ->orderBy('created_at', 'desc');
        return ResponeSuccess(
            '查询成功',
            PaymentOrderResource::collection($list->paginate())
        );
    }

}

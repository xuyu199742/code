<?php

namespace Modules\Game\Http\Controllers;

use App\Rules\UserExist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\WithdrawalOrder;
use Transformers\AccountsInfoTransformer;
use Transformers\WithdrawalOrderTransformer;
use Validator;

class PlayerController extends Controller
{

    private function validatePlayer(Request $request)
    {
        Validator::make($request->all(), [
            'user_id' => ['required', 'numeric', new UserExist('none')],
        ], [
            'user_id.required' => '玩家id不能为空',
            'user_id.numeric'  => '玩家id必须是数字',
        ])->validate();
    }

    public function index(Request $request)
    {
        $this->validatePlayer($request);
        $account_info = AccountsInfo::where('UserID', $request->input('user_id'))->first();
        return ResponeSuccess('查询成功', [
            'RegisterMobile'    => $account_info->RegisterMobile,
            'is_virtual_mobile' => (boolean)preg_match('/^(161|162|165|167|170|171)/',$account_info->RegisterMobile),
            'PlaceName'         => $account_info->PlaceName,
            'RegisterIP'        => $account_info->RegisterIP,
        ]);
    }

    public function checkIp(Request $request)
    {
        $this->validatePlayer($request);
        $account_info = AccountsInfo::where('UserID', $request->input('user_id'))->first();
        $list         = AccountsInfo::where('RegisterIP', $account_info->RegisterIP)->paginate(10);
        return $this->response->paginator($list, new AccountsInfoTransformer());
    }

    public function checkArea(Request $request)
    {
        $this->validatePlayer($request);
        $account_info = AccountsInfo::where('UserID', $request->input('user_id'))->first();
        $address      = $account_info->PlaceName;
        if (!empty($address)) {
            $list=WithdrawalOrder::from(WithdrawalOrder::tableName() . ' as a')
                ->select('a.*','b.GameID')
                ->leftJoin(AccountsInfo::tableName() . ' as b', 'b.UserID', '=', 'a.user_id')
                ->where('b.PlaceName', $address)
                ->paginate(10);
             return $this->response->paginator($list, new WithdrawalOrderTransformer());
        }
        return ResponeFails('没有定位数据');


    }

}

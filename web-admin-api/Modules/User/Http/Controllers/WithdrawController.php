<?php

namespace Modules\User\Http\Controllers;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\GameControlLog;
use Models\AdminPlatform\WithdrawalOrder;
use Transformers\WithdrawalOrderTransformer;
class WithdrawController extends BaseController
{
    /**
     * 获取用户记录
     *
     */
    public function getList()
    {
        $user_id = intval(request('user_id'));
        $list = WithdrawalOrder::where('user_id',$user_id)->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new WithdrawalOrderTransformer());
    }

    /**
     * 绑定银行卡
     * CardName 真实姓名   BankCardID 卡号，PhoneNumber 手机号，BankAddress 开户行
     *
     */
    public function bindCard(){
        \Validator::make(request()->all(), [
            'user_id' => ['required'],
            'CardName'  => ['required'],
            'BankCardID' => ['required','numeric'],
            'PhoneNumber' => ['required','numeric'],
            'BankAddress' => ['required'],
        ], [
            'CardName.required' => '真实姓名必填',
            'BankCardID.numeric' => '银行卡号必须是数字',
            'BankCardID.required' => '银行卡号必填',
            'PhoneNumber.numeric' => '手机号必须是数字',
            'PhoneNumber.required' => '手机号必填',
            'BankAddress.required' => '开户行必填',
        ])->validate();
        $user_id = intval(request('user_id'));
        $game_id= AccountsInfo::where('UserID',$user_id)->value('GameID');
	    $record=AccountsInfo::where('UserID','<>',$user_id)
		    ->where('BankCardID',request('BankCardID'))->first();
	    if($record){
            GameControlLog::addOne('绑定卡号', '给玩家：' .$game_id.' 绑定卡号',GameControlLog::BIND_CORD_NUMBER,GameControlLog::FAILS);
		    return ResponeFails('银行卡号已经被绑定');
	    }
        $data = [
            'CardName' => request('CardName'),
            'BankCardID' => request('BankCardID'),
            'PhoneNumber' => request('PhoneNumber'),
            'BankAddress' => request('BankAddress'),
        ];
        $res = AccountsInfo::where('UserID',$user_id)->update($data);
        if($res){
            GameControlLog::addOne('绑定卡号', '给玩家：' .$game_id.' 绑定卡号',GameControlLog::BIND_CORD_NUMBER,GameControlLog::SUCCESS);
            return ResponeSuccess('修改成功',$data);
        }else{
            GameControlLog::addOne('绑定卡号', '给玩家：' .$game_id.' 绑定卡号',GameControlLog::BIND_CORD_NUMBER,GameControlLog::FAILS);
            return ResponeFails('修改失败');
        }
    }

}

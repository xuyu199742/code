<?php
/*
 |--------------------------------------------------------------------------
 |
 |--------------------------------------------------------------------------
 | Notes:
 | Class AdminUserTransformer
 | User: Administrator
 | Date: 2019/6/20
 | Time: 20:55
 |
 |  * @return
 |  |
 |
 */

namespace Transformers;


use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Agent\AgentWithdrawRecord;

class AgentWithdrawRecordTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['account'];

    public function transform(AgentWithdrawRecord $item)
    {
        return [
            'id'                => $item->id,
            'user_id'           => $item->user_id,
            'order_no'          => $item->order_no,
            'score'             => realCoins($item->score),
            'name'              => $item->name,
            'phonenum'          => $item->phonenum,
            'back_name'         => $item->back_name,
            'back_card'         => $item->back_card,
            'status'            => $item->status,
            'status_text'       => $item->status_text,
            'created_at'        => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
            'updated_at'        => date('Y-m-d H:i:s',strtotime($item->updated_at)) ?? '',
            'username'          => $item->admin->username ?? '',
            'remark'            => $item->remark ?? '',
        ];
    }

    /*关联用户信息表数据*/
    public function includeAccount(AgentWithdrawRecord $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer());
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }

}

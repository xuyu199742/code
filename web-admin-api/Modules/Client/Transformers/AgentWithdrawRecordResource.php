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

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;


class AgentWithdrawRecordResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            //'user_id'           => $this->user_id,
            //'order_no'          => $this->order_no,
            'score'             => realCoins($this->score),
            //'name'              => $this->name,
            //'phonenum'          => $this->phonenum,
            //'back_name'         => $this->back_name,
            //'back_card'         => $this->back_card,
            'status'            => $this->status,
            'created_at'        => date('m-d H:i',strtotime($this->created_at)) ?? '',
        ];
    }

}
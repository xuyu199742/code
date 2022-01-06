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


class MyBrokerageResource extends Resource
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
            'UpdateDate'          => date('Y-m-d',strtotime($this->UpdateDate)),
            'personal_score'      => realCoins($this->personal_score),
            'team_score'          => realCoins($this->team_score),
            'sum_score'           => realCoins($this->sum_score),
        ];
    }

}
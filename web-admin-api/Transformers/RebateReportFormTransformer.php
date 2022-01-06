<?php
/*返利报表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;

class RebateReportFormTransformer extends TransformerAbstract
{
    //返利报表分页
    public function transform($item)
    {
        return [
            'created_at'    => date('Y-m-d',strtotime($item ->created_at)),
            'channel_id'    => $item -> channel_id,
            'return_type'   => $item -> return_type,
            'balance'       =>  realCoins($item ->balance)
        ];
    }

}
<?php
/*用户注册赠送表*/

namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\RegisterGive;


class RegisterGiveTransformer extends TransformerAbstract
{
    //用户注册赠送信息
    public function transform(RegisterGive $item)
    {
        //return $item->toArray();
        return [
            'id'            => $item -> id,
            'score_count'   => realCoins($item -> score_count),
            'score_max'     => realCoins($item -> score_max),
            'platform_type' => $item -> platform_type,
            'give_type'     => $item -> give_type,
            'created_at'    => date('Y-m-d H:i:s',strtotime($item->created_at)),
            'updated_at'    => date('Y-m-d H:i:s',strtotime($item->updated_at)),
        ];
    }

}

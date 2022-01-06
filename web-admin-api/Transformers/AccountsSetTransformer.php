<?php
/*用户信息设置表*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\AccountsSet;
class AccountsSetTransformer extends TransformerAbstract
{
    //用户表基本信息
    public function transform(AccountsSet $item)
    {
        return [
            'nullity' => $item->nullity ?? AccountsSet::NULLITY_ON,//默认启用
            'withdraw' => $item->withdraw ?? AccountsSet::WITHDRAW_ON,//默认启用

            'nullity_text' => $item->nullity_text,
            'withdraw_text' => $item->withdraw_text,
        ];
    }

}

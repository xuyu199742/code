<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\MembersHandsel;


class MembersHandselTransformer extends  TransformerAbstract
{

    public function transform(MembersHandsel $item)
    {
        return [
            "HandselID" => $item->HandselID,
            "HandselName" => MembersHandsel::getTypeName($item),
            "HandselType" => $item->HandselType,
            "HandselDays" => $item->HandselDays,
            "CollectNum" => $item->CollectNum ?? 0,
            "CollectCoins" => realCoins($item->CollectCoins) ?? 0,
        ];
    }

}
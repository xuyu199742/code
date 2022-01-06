<?php

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\MembersInfo;
use Models\Treasure\GameScoreInfo;


class MembersInfoTransformer extends  TransformerAbstract
{

    public function transform(MembersInfo $item)
    {
        $data = $item->toArray();
        $data['HandselCoinsSum']= realCoins($data['HandselCoinsSum']);
        return $data;
    }

}
<?php
/* vip商人*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\VipBusinessman;


class VipBusinessmanTransformer extends TransformerAbstract
{
    //vip商人
    public function transform(VipBusinessman $item)
    {
        if (isset($item->avatar)) {
            $item->avatar_url = asset('storage/' . $item->avatar);
        }
        if (isset($item->admin->username)) {
            $item->username = $item->admin->username;
        }
        $item->gold_coins = realCoins($item->gold_coins ?? 0);
        $item->insure_score = realCoins($item->score->InsureScore ?? 0);
        $item->game_id = $item->account->GameID ?? '';
        return $item->toArray();
    }

}

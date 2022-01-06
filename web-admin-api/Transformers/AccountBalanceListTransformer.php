<?php
namespace Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AccountBalanceListTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        return [
          'balance' => realCoins($item->balance),
          'statistical_date' => Carbon::parse($item->statistical_date)->format('Y-m-d'),
        ];
    }

}

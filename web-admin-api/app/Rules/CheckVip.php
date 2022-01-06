<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\AdminPlatform\VipBusinessman;

class CheckVip implements Rule
{
    private $coins;

    /**
     * Create a new rule instance.
     *
     * @param $coins
     *
     * @return void
     */
    public function __construct($coins)
    {
        $this->coins = $coins;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $man = VipBusinessman::find($value);
        if ($man && $man->gold_coins > $this->coins) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '余额不足';
    }
}

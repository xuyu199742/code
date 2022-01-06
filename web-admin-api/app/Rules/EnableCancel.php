<?php

namespace App\Rules;

use App\Models\Deposit;
use Illuminate\Contracts\Validation\Rule;

class EnableCancel implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $model = Deposit::where($attribute, $value)->first();
        if ($model->status > Deposit::REGISTER) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '订单已经在处理中';
    }
}

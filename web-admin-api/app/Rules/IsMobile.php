<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsMobile implements Rule
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
        if(preg_match('/^(161|162|165|167|170|171)/',$value)){
            return false;
        }
        return preg_match('/^1\d{10}$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '手机号码格式不正确';
    }
}

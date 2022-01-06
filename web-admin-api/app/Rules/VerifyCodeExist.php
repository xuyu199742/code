<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Models\Accounts\CheckCode;

class VerifyCodeExist implements Rule
{
    const LIMIT = 120;

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
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $code = CheckCode::where('PhoneNum', $value)->where('CollectTime', '>', Carbon::now())->orderBy('CollectTime', 'DESC')->first();
        if ($code) {
            if (300 - (strtotime($code->CollectTime) - time()) < self::LIMIT) {
                return false;
            }
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
        return '请不要在' . self::LIMIT . '秒内重复发送';
    }
}

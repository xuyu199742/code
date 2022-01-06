<?php

namespace App\Rules;

use App\Models\ClientSetting;
use Illuminate\Contracts\Validation\Rule;

class ChannelExists implements Rule
{
    private $attribute;
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
        if(ClientSetting::getSettingInfo($attribute, $value)->exists()){
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
        return "渠道不存在";
    }
}

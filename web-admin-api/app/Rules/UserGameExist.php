<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AccountsSet;

class UserGameExist implements Rule
{
    private $message = '用户不存在';




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
        $user=AccountsInfo::where('GameID',$value)->first();
        if ($user) {
            $set = AccountsSet::where('user_id', $user->UserID)->first();
            if ($set) {
                if ($set->nullity == AccountsSet::NULLITY_OFF) {
                    $this->message = '用户已被禁用';
                    return false;
                }
            }
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
        return $this->message;
    }
}

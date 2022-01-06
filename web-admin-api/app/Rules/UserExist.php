<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AccountsSet;

class UserExist implements Rule
{
    private $message = '用户不存在';

    /**
     * all , pay ,withdraw , default all
     *
     * @var string
     */
    private $scenario = 'all';

    /**
     * Create a new rule instance.
     *
     * @param $scenario
     *
     * @value all , pay ,withdraw , default all
     * @return void
     */
    public function __construct($scenario = 'all')
    {
        $this->scenario = $scenario;
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
        if (AccountsInfo::where('UserID', $value)->exists()) {
            $set = AccountsSet::where('user_id', $value)->first();
            if ($set) {
                if ($this->scenario=='none') {
                    return true;
                }
                if ($set->nullity == AccountsSet::NULLITY_OFF) {
                    $this->message = '用户已被禁用';
                    return false;
                }
                if (in_array($this->scenario, ['all', 'withdraw']) && $set->withdraw == AccountsSet::WITHDRAW_OFF) {
                    $this->message = '用户禁止'.config('set.withdrawal');
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

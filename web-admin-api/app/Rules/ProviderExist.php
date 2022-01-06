<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Models\AdminPlatform\PaymentConfig;
use Models\AdminPlatform\PaymentProvider;

class ProviderExist implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $money;

    private $message = '';

    public function __construct()
    {
        $this->money = request('money');
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
        try{
            $provider = PaymentProvider::whereHas('config', function ($query) {
                $query->where('status', PaymentConfig::ON);
            })->where('status', PaymentProvider::ON)
                ->where('id', $value)->first();
            //判断充值通道是否存在
            if (!$provider) {
                $this->message = '支付通道已经关闭';
                return false;
            }
            //判断充值是否合法
            if ($provider->range == PaymentProvider::ON) {
                //1、自定义输入为开启，需要进行区间判断
                if ($this->money > $provider->max_value || $this->money < $provider->min_value) {
                    $this->message = config('set.amount').'不在充值范围内';
                    return false;
                }
            }else{
                //2、自定义输入为关闭状态，对固定值进行判断
                if (!in_array($this->money, explode(',', $provider->pay_value))) {
                    $this->message = '当前自定义充值未开启';
                    return false;
                }
            }
            return true;
        }catch (\Exception $exception){
            return false;
        }
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

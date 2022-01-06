<?php

namespace Models\AdminPlatform;


class SystemSmsTemplate extends Base
{
    protected $table = 'system_sms_template';

    //保存短信日志
    public function saveSmsTemplate($data)
    {
        return $this->save();
    }
}

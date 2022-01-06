<?php

namespace Models\AdminPlatform;


class SystemSmsLog extends Base
{
    protected $table = 'system_sms_log';

    //保存短信日志
    public function saveSmsLog($data)
    {
        $this->user_id   = $data['user_id'] ?? 0;
        $this->type      = 0;
        $this->mobile    = $data['mobile'] ?? '';
        $this->code      = $data['code'] ?? '';
        $this->content   = $data['content'] ?? '';
        $this->is_send   = 0;
        $this->is_usable = 0;
        $this->send_time = date('Y-m-d H:i:s', time());
        $this->dead_time = date('Y-m-d H:i:s', time());
        return $this->save();
    }
}

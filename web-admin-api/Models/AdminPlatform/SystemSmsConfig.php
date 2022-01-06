<?php

namespace Models\AdminPlatform;


class SystemSmsConfig extends Base
{
    protected $table = 'system_sms_config';

    //保存短信
    public function saveSmsConfig($data)
    {
        if (isset($data['id'])){
            $sms = $this->find($data['id']);
            if (!$sms){
                return false;
            }
        }else{
            $sms = new SystemSmsConfig();
        }
        $sms->name   = $data['name'] ?? '';
        $sms->alias  = $data['alias'] ?? '';
        $sms->config = $data['config'] ?? '';
        $sms->weight = $data['weight'] ?? 0;
        $sms->status = $data['status'] ?? 0;
        return $sms->save();
    }
}

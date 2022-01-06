<?php

namespace Models\AdminPlatform;


class SmsLog extends Base
{
    const SUCCESS      = 'SUCCESS';
    const FAIL         = 'FAIL';
    const TYPE_CODE    = 1;
    const TYPE_PROMOTE = 2;

    public static function addLogs($phone, $status, $result, $type = 1)
    {
        if ($status) {
            $status = self::SUCCESS;
        } else {
            $status = self::FAIL;
        }
        if (is_array($result)) {
            $result = json_encode($result);
        }
        $model         = new self();
        $model->type   = $type;
        $model->phone  = $phone;
        $model->status = $status;
        $model->result = $result;
        if ($model->save()) {
            return true;
        }
    }
}

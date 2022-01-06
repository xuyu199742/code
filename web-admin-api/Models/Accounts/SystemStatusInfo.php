<?php
/*系统设置*/
namespace Models\Accounts;

use Models\AdminPlatform\SystemLog;

class SystemStatusInfo extends Base
{
    protected $table            = 'SystemStatusInfo';
    protected $primaryKey       = 'StatusName';
    protected $keyType          = 'string';
    const STATUS_NAME           = 'EnjoinLogon';//系统维护键名

    public $guarded = [''];

    const REGISTER_INFO = [
        'RegisteEmail' => '邮箱',
//        'RegisteInvitationCode' => '邀请码',
        'RegisteMobile' => '手机号',
        'RegistePassword' => '密码',
        'RegisteQQ' => 'QQ',
        'RegisteRealname' => '真实姓名',
        'RegisteRepassword' => '密码验证',
        'RegisteVerificationCode' => '验证码',
        'RegisteWechat' => '微信号',
    ];

    //定义禁止礼金勾选字段
    const FORBID_GIFTS = [
        'reg_give'  => 1,//注册赠送
        'bind_give' => 2,//绑定赠送
    ];

    /*单条保存*/
    public static function saveOne($StatusName)
    {
        $info = request()->all();
        $model              = self::find($StatusName);
        if (!$model) {
            $model = new self();
            $model->StatusName       = $StatusName;
        }
        $model->StatusValue       = $info['StatusValue'] ?? 0;
        if ($StatusName == 'ExperienceScore'){
            $model->StatusValue   = $model->StatusValue * realRatio();
        }
        $model->StatusString      = $info['StatusString'] ?? '';
        $model->StatusTip         = $info['StatusTip'] ?? 0;
        $model->StatusDescription = $info['StatusDescription'] ?? '';
        $model->SortID            = $info['SortID'] ?? 0;
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改系统设置，标识为：'.$model->StatusName);
        });
    }

}

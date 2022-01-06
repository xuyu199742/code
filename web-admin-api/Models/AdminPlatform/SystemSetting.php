<?php

namespace Models\AdminPlatform;
class SystemSetting extends Base
{
    protected $fillable = ['group', 'key', 'value', 'lock'];
    const LOCKED   = 'LOCKED';
    const UNLOCKED = 'UNLOCKED';

    /**
     * 保存系统设置
     *
     * @param string $group 分组标识（目前以框架中的配置文件名来区别的）
     * @param array  $info  配置的键值对
     *
     * @return boolean
     * @throws \Exception
     */
    public function edit($group, $info)
    {
        $config = config($group);
        $locked = [];
        if (isset($config['locked'])) {
            $locked = $config['locked'];
        }
        try {
            foreach ($info as $k => $v) {
                if (in_array($v['key'], $locked)) {
                    $this->updateOrCreate(['group' => $group, 'key' => $v['key']], ['value' => $config[$v['key']] ?? '', 'lock' => self::LOCKED]);
                } else {
                    if ($v['value'] === '') {
                        $this->updateOrCreate(['group' => $group, 'key' => $v['key']], ['value' => $config[$v['key']] ?? '', 'lock' => self::UNLOCKED]);
                    } else {
                        $this->updateOrCreate(['group' => $group, 'key' => $v['key']], ['value' => $v['value'], 'lock' => self::UNLOCKED]);
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改系统配置，id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除系统配置，id为：'.$model->id);
        });
    }

}

<?php
//推广配置表
namespace Models\Agent;



use Models\AdminPlatform\SystemLog;

class AgentRateConfig extends Base
{
    protected $table = 'agent_rate_config';
    protected $primaryKey = 'id';

    /*单条保存*/
    public static function saveOne($id = null)
    {
        $info = request()->all();
        if ($id){
            //编辑
            $model              = self::find($id);
            if (!$model) {
                return false;
            }
        }else{
            //新增
            $model               = new self();
        }
        $model->name         =    $info['name'] ?? '';
        $model->water_min    =    $info['water_min'] ? $info['water_min'] * getGoldBase() : 0;
        $model->rebate       =    $info['rebate'] ?? '';
        $model->category_id  =    $info['category_id'];
        return $model->save($info);
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改代理返利配置，id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除代理返利配置，id为：'.$model->id);
        });
    }
}

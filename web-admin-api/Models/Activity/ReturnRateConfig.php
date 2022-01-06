<?php
/* 活动配置 */
namespace Models\Activity;


class ReturnRateConfig extends Base
{
    //数据表
    protected $table      = 'ReturnRateConfig';
    public    $timestamps = false;
    /*单条保存*/
    public static function saveOne($id = null)
    {
        $model              = self::find($id);
        if (!$model) {
            $model          = new self();
        }
        $model->activity_id = request('activity_id', 0);
        $model->score       = request('score', 0);
        $model->rate        = request('rate', 0);
        return $model->save();
    }
}

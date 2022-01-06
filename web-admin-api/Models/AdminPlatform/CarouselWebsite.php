<?php

namespace Models\AdminPlatform;


class CarouselWebsite extends Base
{
    //数据表
    protected $table = 'carousel_website';

    public static function saveOne($id = null)
    {
        try{
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
            $model->url         = $info['url'] ?? '';
            return $model->save();
        }catch (\Exception $exception){
            return false;
        }
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改轮播网址，id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除轮播网址，id为：'.$model->id);
        });
    }
}

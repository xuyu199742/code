<?php

namespace Models\AdminPlatform;

/**
 * NoticeID            int              公告标识
 * NoticeTitle         nvarchar(50)     公告标题/活动名称
 * MoblieContent       nvarchar(1000)   //手机内容
 * WebContent          ntext            //网站内容
 * SortID              int              排序 正序
 * Publisher           nvarchar(32)     发布人
 * PublisherTime       datetime         发布时间
 * IsHot               bit              是否热门
 * IsTop               bit              是否置顶
 * Nullity             bit              是否禁用
 * PlatformType        int              版本类型：0、全部，1、H5，2、U3D
 * is_img              tinyint          是否使用图片，0否，1是
 * img                 varchar(255)     活动详情（图片）
 * content             text             活动详情（富文本）
 * remark              varchar(255)     备注
 */
class SystemNotice extends Base
{
    //数据表
    protected $table = 'SystemNotice';
    protected $primaryKey = 'NoticeID';
    public    $timestamps = false;
    const PLATFORM_ALL = 0;//所有
    const PLATFORM_H5 = 1;//h5
    const PLATFORM_U3D = 2;//U3D
    const PLATFORM_TYPE = [
        self::PLATFORM_ALL          => '所有',
        self::PLATFORM_H5           => 'H5',
        self::PLATFORM_U3D          => 'U3D',
    ];
    const TYPE = [
        'h5' => 1,
        'u3d'  => 2,
    ];
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改系统公告，公告标识为：'.$model->NoticeID);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除系统公告，公告标识为：'.$model->NoticeID);
        });
    }

    // 获取游戏公告
    static function GetList($type)
    {
        if(!in_array($type,['u3d','h5'])){
            return [];
        }
        $type_arr = [0];
        if(key_exists($type,self::TYPE)){
            $type_arr[] = self::TYPE[$type];
        }
        try {
            $data = self::whereIn('PlatformType',$type_arr)
                ->select('NoticeID as id','NoticeTitle as name')
                ->orderBy('SortID','asc')
                ->orderBy('PublisherTime','desc')
                ->get()
                ->toArray();
            return $data;
        }catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }

    // 获取活动详情
    static function GetDetail($id)
    {
        try {
            $data = self::where('NoticeID', $id)
                ->select('NoticeID','NoticeTitle','SortID','PublisherTime','PlatformType','remark','is_img','img','content')
                ->first();
            if($data) {
                $data['content'] = stripslashes(htmlspecialchars_decode($data['content']));
                $data['created_at'] = substr($data['created_at'],0,10);
            }
            return $data;
        }catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/9
 * Time: 11:34
 */
namespace Models\AdminPlatform;


class Dict extends Base
{
    protected $guarded = [];
    protected $table      = 'dict';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    const DICT_PID = [
        1 => [ 'name' => '活动', 'url' => 'activities' , 'sort' => 1 ],
        2 => [ 'name' => '任务', 'url' => '', 'sort' => 2 ],
        3 => [ 'name' => '公告', 'url' => 'noticeDetail', 'sort' => 3 ],
        9 => [ 'name' => '配置背景颜色', 'url' => '', 'sort' => 9 ],
    ];
    // 精彩活动——分类
    const DICT_ID = [
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 8,
        5 => 16,
        6 => 32,
        7 => 64,
        8 => 128,
    ];

    // 客户端返回有数据部分的分类
    static function GetEffective($type){
        if(!in_array($type,['u3d','h5'])){
            return [];
        }
        $res = Dict::where('pid', 1)
            ->where('status',1)
            ->whereIn('id',Activities::GetDictArr($type))
            ->select('id', 'name', 'sort', 'status')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
        return $res ?? [];
    }
}

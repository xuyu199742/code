<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/9
 * Time: 11:34
 */
namespace Models\AdminPlatform;


use Matrix\Exception;

class Activities extends Base
{
    protected $guarded = [];
    protected $table      = 'activities';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    const TYPE = [
      'u3d' => 1,
      'h5'  => 2,
    ];

    // 后台——获取活动列表
    static function GetList($dict_id)
    {
        try {
            $list = Activities::where(\DB::raw("(dict_ids & " . Dict::DICT_ID[$dict_id] . ")"), '>', 0)
                ->select('id','name','dict_ids','type','switch','created_at','sort','remark','img','is_img')
                ->orderBy('sort')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            foreach ($list as $k1 => $val) {
                $arr = [];
                foreach (Dict::DICT_ID as $k2 => $item) {
                    if (($val['dict_ids'] & $item) > 0) {
                        $arr[] = $k2;
                    }
                }
                $list[$k1]['dict_ids'] = implode(',', $arr);
                $list[$k1]['created_at'] = substr($val['created_at'],0,10);
            }
            return $list;
        }catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }

    // 客户端——获取活动列表
    static function GetListClient($dict_id, $type_arr = [])
    {
        try {
            $list = Activities::where(\DB::raw("(dict_ids & " . Dict::DICT_ID[$dict_id] . ")"), '>', 0)
                ->where('switch',1)
                ->whereIn('type',$type_arr)
                ->select('id','name','dict_ids','type','switch','created_at','sort','remark','img','is_img')
                ->orderBy('sort')
                ->orderBy('created_at', 'desc')
                ->paginate(200);
            foreach ($list as $k1 => $val) {
                $arr = [];
                foreach (Dict::DICT_ID as $k2 => $item) {
                    if (($val['dict_ids'] & $item) > 0) {
                        $arr[] = $k2;
                    }
                }
                $list[$k1]['dict_ids'] = implode(',', $arr);
                $list[$k1]['created_at'] = substr($val['created_at'],0,10);
            }
            return $list;
        }catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }
    // 获取活动详情
    static function GetDetail($id)
    {
        try {
            $data = Activities::where('id', $id)->first();
            if($data) {
                $data['content'] = stripslashes(htmlspecialchars_decode($data['content']));
                $data['created_at'] = substr($data['created_at'],0,10);
            }
            return $data;
        }catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }

    // 返回有效活动所属分类dict_ids集合
    static function GetDictArr($type){
       $arr = [];
       $type_arr = [0];
       if(key_exists($type,Activities::TYPE)){
           $type_arr[] = Activities::TYPE[$type];
        }
       $res = Activities::whereIn('type',$type_arr)->pluck("dict_ids")->toArray();
       if($res) {
            $new = array_unique($res);
            foreach ($new as $k1 => $v1){
                foreach (Dict::DICT_ID as $k2 => $v2){
                    if (($v1 & $v2) >0){
                        $arr[] = $k2;
                    }
                }
            }
       }
       return array_unique($arr);
    }
}

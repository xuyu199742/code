<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/11
 * Time: 13:44
 */
namespace App\Http\Controllers;

use Models\AdminPlatform\Activities;
use Models\AdminPlatform\SystemNotice;
use Models\AdminPlatform\Dict;

class ActivitiesController extends Controller
{
    // 精彩活动列表
    public function index($type, $id)
    {
        try {
            if($type == 'mobile') $type = 'h5';
            if(key_exists($type, Activities::TYPE)){
                $type_arr = [0,Activities::TYPE[$type]];
            } else {
                $type_arr = array_merge([0],Activities::TYPE);
            }
            $dict_id = $id ?? 1;
            $list = Activities::GetListClient($dict_id, $type_arr);
            $bgColor = Dict::where('id',9)->select('extend')->first();
            return view('activities', ['data'=> $list,'bgColor'=>$bgColor['extend']]);
        } catch (Exception $e){
            return ResponeFails('操作有误');
        }

    }
    // 活动详情
    public function detail($id)
    {
        $data = Activities::GetDetail($id);
        $bgColor = Dict::where('id',9)->select('extend')->first();
        return view('activityDetail', ['data'=>$data,'bgColor'=>$bgColor['extend']]);
    }
    // 活动详情预览页面
    public function detailShow($id)
    {
        $data = Activities::GetDetail($id);
        $bgColor = Dict::where('id',9)->select('extend')->first();
        return view('activityDetailShow', ['data'=>$data,'bgColor'=>$bgColor['extend']]);
    }
    // 游戏公告详情及预览
    public function noticeDetail($id)
    {
        $data = SystemNotice::GetDetail($id);
        $bgColor = Dict::where('id',10)->select('extend')->first();
        return view('noticeDetail', ['data'=>$data,'bgColor'=>$bgColor['extend']]);
    }
    // 游戏公告详情及预览
    public function kfProblemDetail()
    {
        $extend = Dict::where('id',11)->value('extend');
        $data['content'] = stripslashes(htmlspecialchars_decode($extend));
        $bgColor = Dict::where('id',12)->select('extend')->first();
        return view('kfProblemDetail', ['data'=>$data,'bgColor'=>$bgColor['extend']]);
    }
}

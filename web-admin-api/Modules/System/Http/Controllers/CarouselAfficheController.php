<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\CarouselAffiche;
use Models\AdminPlatform\SystemSetting;
use Transformers\CarouselAfficheTransformer;

class CarouselAfficheController extends Controller
{
    //轮播广告列表
    public function getList()
    {
        $list = CarouselAffiche::paginate(config('page.list_rows'));
        return $this->response->paginator($list, new CarouselAfficheTransformer());
    }

    //轮播广告新增
    public function add()
    {
        $res = CarouselAffiche::saveOne();
        if (!$res){
            return ResponeFails('添加失败');
        }
        return ResponeSuccess('添加成功');
    }

    //轮播广告编辑
    public function edit($id)
    {
        $res = CarouselAffiche::saveOne($id);
        if (!$res){
            return ResponeFails('修改失败');
        }
        return ResponeSuccess('修改成功');
    }

    //轮播广告删除
    public function del($id)
    {
        $res = CarouselAffiche::where('id',$id)->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }

    //轮播时长设置
    public function carousel_affiche_setTimes()
    {
        if (\request()->isMethod('get')){
            $SystemSetting = SystemSetting::where('group','carousel')->where('key','ads')->first();
            $list['ads'] = $SystemSetting->value ?? 1;//相隔时长
            return ResponeSuccess('获取成功',$list);
        }elseif (\request()->isMethod('post')){
            $times = \request('times');
            $res = SystemSetting::updateOrCreate(['group' => 'carousel', 'key' => 'ads'], ['value' => $times]);
            if (!$res){
                return ResponeFails('设置失败');
            }
            return ResponeSuccess('设置成功');
        }
    }
}

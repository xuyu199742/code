<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\CarouselWebsite;
use Models\AdminPlatform\SystemSetting;
use Transformers\CarouselWebsiteTransformer;

class CarouselWebsiteController extends Controller
{
    //轮播网址列表
    public function getList()
    {
        $list = CarouselWebsite::paginate();
        return $this->response->paginator($list, new CarouselWebsiteTransformer());
    }

    //轮播网址新增
    public function add()
    {
        $res = CarouselWebsite::saveOne();
        if (!$res){
            return ResponeFails('添加失败');
        }
        return ResponeSuccess('添加成功');
    }

    //轮播网址编辑
    public function edit($id)
    {
        $res = CarouselWebsite::saveOne($id);
        if (!$res){
            return ResponeFails('修改失败');
        }
        return ResponeSuccess('修改成功');
    }

    //轮播网址删除
    public function del($id)
    {
        $res = CarouselWebsite::where('id',$id)->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }

    //轮播时长设置
    public function setTimes()
    {
        if (\request()->isMethod('get')){
            $SystemSetting = SystemSetting::where('group','carousel')->where('key','times')->first();
            $list['times'] = $SystemSetting->value ?? 1;//相隔时长
            return ResponeSuccess('获取成功',$list);
        }elseif (\request()->isMethod('post')){
            $times = \request('times');
            $res = SystemSetting::updateOrCreate(['group' => 'carousel', 'key' => 'times'], ['value' => $times]);
            if (!$res){
                return ResponeFails('设置失败');
            }
            return ResponeSuccess('设置成功');
        }
    }

}

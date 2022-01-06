<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/9
 * Time: 11:38
 */

namespace Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use DemeterChain\A;
use Illuminate\Http\Request;
use Matrix\Exception;
use Models\AdminPlatform\Activities;
use Models\AdminPlatform\Dict;
//use Models\AdminPlatform\Activities;
use Validator;

class ActivitiesController extends Controller
{
   /**
     * 获取活动分类列表
     *
     */
    public function getActivitiesDict()
    {
        $list['sort'] = Dict::where('pid',1)->select('id','name','sort','status')->orderBy('sort')->orderBy('id')->get();
        $list['bgColor'] = Dict::where('id',9)->select('id','name','extend')->first();
        return ResponeSuccess('Success',$list);
    }

    /**
     * 配置背景色
     *
     */
    public function setBgColor(Request $request)
    {
        $request->validate([
            'id' => 'integer|in:9,10',
            'extend' => 'required',
        ], [
            'id.integer' => '请求参数有误',
            'id.in' => '请求参数有误',
            'extend.required' => '必须选择背景色',
        ]);
        try {
            $res = Dict::where('id',request('id'))->update(['extend' => $request->extend]);
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 活动分类编辑
     *
     */
    public function setActivitiesDict()
    {
        Validator::make(request()->all(), [
            'id' => 'integer',
            'name' => 'nullable',
            'status' => 'integer|in:0,1',
        ], [
            'id.integer' => '分类ID必须数字',
            'name.nullable' => '分类名称不能为空',
            'status.integer' => '状态不正确',
        ])->validate();
        try {
            $data = [
                'name'=>request('name'),
                'status'=>request('status'),
            ];
            $res = Dict::where('id',request('id'))->update($data);
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 活动分类状态批量编辑
     *
     */
    public function setDictStatus(Request $request)
    {
        $res = 0;
        try {
            $id_arr = $request->input('id',array());
            $status_arr = $request->input('status',array());
            if($id_arr && $status_arr) {
                $len = count($id_arr);
                $sql = "UPDATE dict SET status = CASE ";
                for ($i=0; $i<$len; $i++) {
                    $sql .= " WHEN id = {$id_arr[$i]} THEN {$status_arr[$i]} ";
                }
                $ids_str = implode(',',$id_arr);
                $sql .= " END WHERE id IN ({$ids_str});";
                $res = \DB::update(\DB::raw($sql));
                if (!$res) {
                    return ResponeFails('操作失败');
                }
            } else {
                return ResponeFails('内容没有变化');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 获取活动列表
     *
     */
    public function getActivities(Request $request)
    {
        $request->validate([
            'dict_id' => 'integer',
        ]);
        try {
            $dict_id = $request->input('dict_id',1);
            $list = Activities::GetList($dict_id);
            return ResponeSuccess('Success',$list);
        } catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }

    /**
     * 设置活动所属分类
     *
     */
    public function setDictids(Request $request)
    {
        $request->validate([
            'id' => 'integer',
            'dict_ids' => 'string',
        ]);
        try {
            $dict_ids = $request->input('dict_ids',1);
            $dict_arr = explode(',',$dict_ids);
            $dict_num = 0;
            foreach ($dict_arr as $val){
                if(key_exists($val,Dict::DICT_ID)){
                    $dict_num += Dict::DICT_ID[$val];
                }
            }
            $res = Activities::where('id',$request->id)->update(['dict_ids'=>$dict_num]);
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 获取单个活动详情
     *
     */
    public function getActivityDetail($id)
    {
        try {
            $data = Activities::GetDetail($id);
            return ResponeSuccess('Success',$data);
        } catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }

    /**
     * 新增或编辑活动
     *
     */
    public function setActivity(Request $request)
    {
        $request->validate([
            'id' => 'integer',
            'name' => 'required',
            'type' => 'integer|in:0,1,2',
            'switch' => 'integer|in:0,1',
            'sort' => 'integer',
            'remark' => 'nullable',
            'img' => 'nullable',
            'is_img' => 'integer|in:0,1',
        ]);
        $content = $request->input('content','?') ?? '';
        try {
            $data = [
                'name' => $request->input('name'),
//                'dict_ids' => $request->input('dict_ids','1'),
                'type' => $request->input('type',0),
                'switch' => $request->input('switch',0),
                'created_at' => $request->input('created_at',date('Y-m-d H:i:s')),
                'sort' => $request->input('sort',0),
                'remark' => $request->input('remark','') ?? '',
                'img' => $request->input('img','') ?? '',
                'is_img' => $request->input('is_img',0),
                'img2' => $request->input('img2','') ?? '',
                'content' => htmlspecialchars($content),
            ];
            if($request->input('id')) {
                $res = Activities::where('id', $request->input('id'))->update($data);
            } else {
                $m = new Activities();
                $res = $m->insertGetId($data);
            }
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功', $res);
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 删除活动
     *
     */
    public function delActivity(Request $request)
    {
        $request->validate([
            'id' => 'integer',
        ]);
        try {
            $res = Activities::where('id', $request->input('id'))->delete();
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
    }

    /**
     * 上传富文本文件 （未使用，有公共接口）
     * @param dir  上传类型，分别为image、flash、media、file
     */
    public function upload(Request $request)
    {
        Validator::make($request->all(), [
            'dir' => 'required|in:image,flash,media,file',
        ],[
            'dir.required' => '缺少参数',
            'dir.in'    => '参数不正确',
        ])->validate();
        if ($request->file('imgFile')->isValid()) {
            $path = $request->image->store('ads_images', 'public');
            $real_path = $request->file('imgFile')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'ads_images/'.pathinfo($path)['basename']);
            if ($bool) {
                return \Response::json(['url' => $path, 'error' => 0]);
            }

        }
        return \Response::json(['message' => '上传失败', 'error' => 1]);
    }

}

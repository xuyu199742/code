<?php

namespace Modules\Agent\Http\Controllers;

use Illuminate\Http\Response;
use Models\Agent\AgentRateConfig;
use Modules\Agent\Http\Requests\AgentRateConfigRequest;

class AgentRateConfigController extends BaseController
{
    /**
     * 获取代理返利配置列表
     *
     */
    public function getList()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
        ], [
            'category_id.required' => '游戏分类值必传',
            'category_id.integer'  => '游戏分类值是整数',
        ])->validate();
        $list = AgentRateConfig::where('category_id',request('category_id'))->orderBy('water_min','desc')->get();
        foreach ($list as $k => $v){
            $list[$k]['water_min'] = strval($list[$k]['water_min'] / getGoldBase());
            if (isset($list[$k-1])){
                $list[$k]['water_max'] = $list[$k-1]['water_min'] - 1;
            }else{
                $list[$k]['water_max'] = '上不封顶';
            }
        }
        return ResponeSuccess('请求成功',$list);
    }

    /**
     * 代理返利配置添加
     *
     */
    public function add(AgentRateConfigRequest $request)
    {
        $counts= AgentRateConfig::where('water_min',request('water_min')  * getGoldBase())
            ->where('category_id',request('category_id'))
            ->count();
        if($counts>0){
            return ResponeFails('业绩区间下限不能重复！');
        }else{
            $res = AgentRateConfig::saveOne();
            if (!$res){
                return ResponeFails('添加失败');
            }
            return ResponeSuccess('添加成功');
        }
    }

    /**
     * 代理返利配置编辑
     *
     */
    public function edit(AgentRateConfigRequest $request)
    {
        $counts= AgentRateConfig::where('id','<>',request('id'))
            ->where('category_id',request('category_id'))
            ->where('water_min',request('water_min') * getGoldBase())
            ->count();
        if($counts>0){
            return ResponeFails('业绩区间下限不能重复!');
        }else{
            $res = AgentRateConfig::saveOne(request('id'));
            if (!$res){
                return ResponeFails('修改失败');
            }
            return ResponeSuccess('修改成功');
        }
    }

    /**
     * 代理返利配置删除
     *
     */
    public function del()
    {
        $id = request('id');
        $AgentRateConfig = AgentRateConfig::find($id);
        if (!$AgentRateConfig){
            return ResponeFails('该数据不存在');
        }
        $res = $AgentRateConfig->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }
}

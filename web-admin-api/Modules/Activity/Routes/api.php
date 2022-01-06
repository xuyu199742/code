<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');
$api->version('v1',function ($api) {

    //活动管理路由
    $api->group(['prefix' => 'activity', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Activity\Http\Controllers'], function ($api) {
        //注册，绑定赠送
        $api->get('registerGiveList', ['uses' => 'RegisterGiveController@register_give_list', 'permission' => ['admin', '注册赠送', '注册赠送', 'v1']])
            ->name('activity.register_give_list');   // 注册，绑定赠送列表
        $api->post('registerGiveAdd', ['uses' => 'RegisterGiveController@register_give_add', 'permission' => ['admin', '注册赠送新增', '注册赠送', 'v1']])
            ->name('activity.register_give_add');   // 注册，绑定赠送新增
        $api->post('registerGiveEdit', ['uses' => 'RegisterGiveController@register_give_edit', 'permission' => ['admin', '注册赠送编辑', '注册赠送', 'v1']])
            ->name('activity.register_give_edit');   // 注册，绑定赠送编辑
        $api->post('registerGiveDelete', ['uses' => 'RegisterGiveController@register_give_delete', 'permission' => ['admin', '注册赠送删除', '注册赠送', 'v1']])
            ->name('activity.register_give_delete');   // 注册，绑定赠送删除
        //转盘
        $api->get('TurntableRewardList', ['uses' => 'TurntableController@turntable_reward_list', 'permission' => ['admin', '转盘奖励配置列表', '转盘管理', 'v1']])
            ->name('activity.turntable_reward_list'); // 转盘奖励配置列表
        $api->post('TurntableRewardSave', ['uses' => 'TurntableController@turntable_reward_save', 'permission' => ['admin', '转盘奖励配置', '转盘管理', 'v1']])
            ->name('activity.turntable_reward_save');   //转盘奖励配置保存
        $api->post('TurntableRewardDelete', ['uses' => 'TurntableController@turntable_reward_delete', 'permission' => ['admin', '转盘奖励配置删除', '转盘管理', 'v1']])
            ->name('activity.turntable_reward_delete');   //转盘奖励配置删除
        $api->get('TurntableEffectList', ['uses' => 'TurntableController@turntable_effect_list', 'permission' => ['admin', '转盘功能配置列表', '转盘管理', 'v1']])
            ->name('activity.turntable_effect_list'); // 转盘功能配置列表
        $api->post('TurntableEffectSave', ['uses' => 'TurntableController@turntable_effect_save', 'permission' => ['admin', '转盘功能配置', '转盘管理', 'v1']])
            ->name('activity.turntable_effect_save');   //转盘功能配置保存
        $api->get('TurntableRankList', ['uses' => 'TurntableController@turntable_rank_list', 'permission' => ['admin', '转盘功能配置', '转盘管理', 'v1']])
            ->name('activity.turntable_rank_list');
        $api->post('TurntableUpdateStatus', ['uses' => 'TurntableController@turntable_update_status', 'permission' => ['admin', '转盘功能配置', '转盘管理', 'v1']])
            ->name('activity.turntable_update_status');
        //红包
        $api->get('RedPacketList', ['uses' => 'RedPacketController@red_packet_list', 'permission' => ['admin', '红包奖励配置列表', '红包管理', 'v1']])
            ->name('activity.red_packet_list'); // 红包奖励配置列表
        $api->post('RedPacketSave', ['uses' => 'RedPacketController@red_packet_save', 'permission' => ['admin', '红包奖励配置', '红包管理', 'v1']])
            ->name('activity.red_packet_save'); // 红包奖励配置保存
        /*$api->post('RedPacketDelete', ['uses' => 'RedPacketController@red_packet_delete', 'permission' => ['admin', '红包奖励配置删除', '红包管理', 'v1']])
            ->name('activity.red_packet_delete'); */// 红包奖励配置删除
        $api->get('RedPacketEffectList', ['uses' => 'RedPacketController@red_packet_effect_list', 'permission' => ['admin', '红包功能配置列表', '红包管理', 'v1']])
            ->name('activity.red_packet_effect_list'); // 红包功能配置列表
        $api->post('RedPacketEffectSave', ['uses' => 'RedPacketController@red_packet_effect_save', 'permission' => ['admin', '红包功能配置', '红包管理', 'v1']])
            ->name('activity.red_packet_effect_save');   //红包功能配置保存
        //任务活动列表
        $api->get('getTaskList', ['uses' => 'ActivityTaskController@getTaskActivityList', 'permission' => ['admin', '任务活动列表', '任务活动', 'v1']])
            ->name('activity.task_list');
        //任务活动时间显示
        $api->get('taskTimeShow', ['uses' => 'ActivityTaskController@taskActivityTimeShow', 'permission' => ['admin', '任务活动时间显示', '任务活动', 'v1']])
            ->name('activity.task_time_show');
        //任务活动时间配置
        $api->post('taskTimeConfig', ['uses' => 'ActivityTaskController@taskActivityTimeConfig', 'permission' => ['admin', '任务活动时间保存', '任务活动', 'v1']])
            ->name('activity.task_time_config');
        //任务活动配置
        $api->post('taskConfig', ['uses' => 'ActivityTaskController@taskActivityConfig', 'permission' => ['admin', '任务活动配置保存', '任务活动', 'v1']])
            ->name('activity.task_activity_config');
        //任务活动配置详情
        $api->get('taskConfigDetails', ['uses' => 'ActivityTaskController@taskConfigDetails', 'permission' => ['admin', '任务活动配置详情', '任务活动', 'v1']])
            ->name('activity.task_config_details');
        //任务活动领取记录
        $api->get('taskRecord', ['uses' => 'ActivityTaskController@taskActivityRecord', 'permission' => ['admin', '任务活动领取记录', '任务活动', 'v1']])
            ->name('activity.task_activity_record');
        //软删除任务活动配置
        $api->delete('taskConfigDelete/{id}', ['uses' => 'ActivityTaskController@taskConfigDelete', 'permission' => ['admin', '删除任务活动配置', '任务活动', 'v1']])
            ->name('activity.task_config_delete');
        //任务游戏联动房间
        $api->get('gameJoinRoom', ['uses' => 'ActivityTaskController@gameJoinRoom', 'permission' => ['admin', '游戏名称和房间等级', '任务活动', 'v1']])
            ->name('activity.game_join_room');
        //返利活动列表
        $api->get('rebateActList', ['uses' => 'RebateConfigController@rebateActList', 'permission' => ['admin', '返利活动列表', '活动管理', 'v1']])->name('activity.rebate_act_list');
        //返利活动新增
        $api->post('rebateActAdd', ['uses' => 'RebateConfigController@rebateActAdd', 'permission' => ['admin', '返利活动新增', '活动管理', 'v1']])->name('activity.rebate_act_add');
        //返利活动编辑
        $api->match(['get','post'],'rebateActEdit/{id}', ['uses' => 'RebateConfigController@rebateActEdit', 'permission' => ['admin', '返利活动编辑', '活动管理', 'v1']])->name('activity.rebate_act_edit');
        //返利活动删除
        $api->delete('rebateActDel/{id}', ['uses' => 'RebateConfigController@rebateActDel', 'permission' => ['admin', '返利活动删除', '活动管理', 'v1']])->name('activity.rebate_act_del');
        //返利活动日志
        $api->get('rebateActLog', ['uses' => 'RebateConfigController@rebateActLog', 'permission' => ['admin', '返利活动日志', '活动管理', 'v1']])->name('activity.rebate_act_log');
        //排行活动时间配置
        $api->match(['get','post'],'rankingSetTime', ['uses' => 'RankingController@setTime', 'permission' => ['admin', '排行活动时间配置', '活动管理', 'v1']])->name('activity.ranking_set_time');
        //排行活动列表
        $api->get('rankingList', ['uses' => 'RankingController@getList', 'permission' => ['admin', '排行活动列表', '活动管理', 'v1']])->name('activity.ranking_list');
        //排行活动新增
        $api->post('rankingAdd', ['uses' => 'RankingController@add', 'permission' => ['admin', '排行活动新增', '活动管理', 'v1']])->name('activity.ranking_add');
        //排行活动编辑
        $api->match(['get','post'],'rankingEdit/{id}', ['uses' => 'RankingController@edit', 'permission' => ['admin', '排行活动编辑', '活动管理', 'v1']])->name('activity.ranking_edit');
        //排行活动删除
        $api->delete('rankingDel/{id}', ['uses' => 'RankingController@del', 'permission' => ['admin', '排行活动删除', '活动管理', 'v1']])->name('activity.ranking_del');
        //排行活动详情
        $api->get('rankingDetails', ['uses' => 'RankingController@details', 'permission' => ['admin', '排行活动详情', '活动管理', 'v1']])->name('activity.ranking_details');
        //获取游戏类型
        $api->get('getKindType', ['uses' => 'RankingController@getKindType', 'permission' => ['admin', '排行活动获取游戏类型', '活动管理', 'v1']])->name('activity.get_kind_type');
        //设置转盘，红包，任务，返利，排行活动状态
        $api->get('setActivityStatus', ['uses' => 'ActivityTaskController@setActivityStatus', 'permission' => ['admin', '设置活动状态', '活动管理', 'v1']])->name('activity.set_status');
        // 添加或修改常规活动
        $api->get('getActivity/{pid}', ['uses' => 'ActivitiesNormalController@getActivity', 'permission' => ['admin', '获取常规活动', '活动管理', 'v1']])->name('activity.get_activity');
        // 添加或修改常规活动
        $api->post('saveActivity/{pid}', ['uses' => 'ActivitiesNormalController@saveActivity', 'permission' => ['admin', '配置常规活动', '活动管理', 'v1']])->name('activity.save_activity');
        // 获取答题活动配置
        $api->get('getAnswerGive', ['uses' => 'ActivitiesNormalController@getAnswerGive', 'permission' => ['admin', '答题活动', '获取答题活动配置', 'v1']])->name('activity.answer_give_config');
        // 答题活动保存
        $api->post('answerGiveSave', ['uses' => 'ActivitiesNormalController@answerGiveSave', 'permission' => ['admin', '答题活动', '答题活动保存', 'v1']])->name('activity.answer_give_save');
    });
});

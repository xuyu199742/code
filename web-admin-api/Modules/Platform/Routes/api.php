<?php
$api = app('Dingo\Api\Routing\Router');
$api->version('v1',function ($api) {
    //活动管理路由
    $api->group(['prefix' => 'platform', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Platform\Http\Controllers'], function ($api) {
        //平台列表
        $api->get('platformList', ['uses' => 'PlatformController@platformList', 'permission' => ['admin', '平台列表', '游戏控制', 'v1']])->name('platform.platform_list');
        //平台编辑
        $api->match(['get','post'],'platformEdit/{alias}', ['uses' => 'PlatformController@platformEdit', 'permission' => ['admin', '平台编辑', '游戏控制', 'v1']])->name('platform.platform_edit');
        //平台游戏列表
        $api->get('platformGameList', ['uses' => 'PlatformController@platformGameList', 'permission' => ['admin', '平台游戏列表', '游戏分类', 'v1']])->name('platform.platform_game_list');
        //平台游戏编辑
        //$api->match(['get','post'],'platformGameEdit', ['uses' => 'PlatformController@platformGameEdit', 'permission' => ['admin', '平台游戏编辑', '游戏控制', 'v1']])->name('platform.platform_game_edit');
        //外接平台注单
        $api->get('gameDripSheet', ['uses' => 'PlatformController@gameDripSheet', 'permission' => ['admin', '平台注单', '注单管理', 'v1']])->name('platform.platform_game_dripsheet');
        //外接平台进出记录
        $api->get('platformInOutRecord', ['uses' => 'PlatformController@platformInOutRecord', 'permission' => ['admin', '平台进出', '注单管理', 'v1']])->name('platform.platform_inout_record');
        //平台游戏处理遗漏注单
        $api->get('gameMissDripSheet', ['uses' => 'PlatformController@gameMissDripSheet', 'permission' => ['admin', '遗漏注单', '注单管理', 'v1']])->name('platform.game_miss_dripsheet');
        //外接平台游戏处理遗漏注单
        $api->post('replacementGameRecore', ['uses' => 'PlatformController@replacementGameRecore', 'permission' => ['admin', '外接平台游戏处理遗漏注单', '平台游戏管理', 'v1']])->name('platform.replacement_game_recore');
        //下拉框选择平台和游戏
        $api->get('getPlatformOrGame', ['uses' => 'PlatformController@getPlatformOrGame']);
        //下拉框选择平台
        $api->get('getPlatform', ['uses' => 'PlatformController@getPlatform']);
        //下拉框选择平台游戏
        $api->get('getPlatformGame', ['uses' => 'PlatformController@getPlatformGame']);
        // 选择分类对应的游戏
        $api->get('getCategoryForGame', ['uses' => 'PlatformController@getCategoryForGame']);
        //获取公司
        $api->get('getCompany', ['uses' => 'PlatformController@getCompany']);

        //棋牌游戏
      //  $api->get('getPlatformList', ['uses' => 'PlatformController@getPlatformList', 'permission' => ['admin', '棋牌游戏', '平台游戏管理', 'v1']])->name('platform.get_platform_list');
        //棋牌游戏添加或编辑平台
      //  $api->match(['get','post'],'savePlatform/{id}', ['uses' => 'PlatformController@platformEdit', 'permission' => ['admin', '棋牌游戏添加或编辑', '平台游戏管理', 'v1']])->name('platform.platform_edit');

        //添加洗码设置
        $api->post('createWashCodeSetting', ['uses' => 'WashCodeSettingController@create', 'permission' => ['admin', '添加洗码', '洗码设置', 'v1']])->name('wash_code_setting.create');
        $api->put('updateWashCodeSetting/{id}', ['uses' => 'WashCodeSettingController@update', 'permission' => ['admin', '编辑洗码', '洗码设置', 'v1']])->name('wash_code_setting.update');
        $api->delete('deleteWashCodeSetting', ['uses' => 'WashCodeSettingController@delete', 'permission' => ['admin', '删除洗码', '洗码设置', 'v1']])->name('wash_code_setting.delete');
        $api->get('getWashCodeSetting', ['uses' => 'WashCodeSettingController@list', 'permission' => ['admin', '洗码列表', '洗码设置', 'v1']])->name('wash_code_setting.list');

        //洗码记录列表
        $api->get('wash_code/list', ['uses' => 'WashCodeRecordController@getList', 'permission' => ['admin', '获取洗码记录列表', '洗码记录', 'v1']])->name('wash_code_record.list');
        //洗码记录详情
        $api->get('wash_code/detail', ['uses' => 'WashCodeRecordController@getDetail', 'permission' => ['admin', '获取洗码记录详情', '洗码记录', 'v1']])->name('wash_code_record.detail');
        //查询游戏详情
        $api->get('gameDetailsRecord', ['uses' => 'GameController@gameDetailsRecord', 'permission' => ['admin', '游戏记录详情', '平台游戏管理', 'v1']])->name('platform.game_details_record');
        //设置洗码领取开始时间
        $api->get('setWashCodeStartTime', ['uses' => 'WashCodeRecordController@setWashCodeStartTime', 'permission' => ['admin', '设置洗码领取计算时间', '洗码记录', 'v1']])->name('wash_code.set_start_time');

        //精彩活动
        // 获取活动分类
        $api->get('getActivitiesDict', ['uses' => 'ActivitiesController@getActivitiesDict', 'permission' => ['admin', '获取活动分类', '精彩活动', 'v1']])->name('platform.get_activities_dict');
        // 配置背景色
        $api->post('setBgColor', ['uses' => 'ActivitiesController@setBgColor', 'permission' => ['admin', '配置背景色', '精彩活动', 'v1']])->name('platform.set_bgcolor');
        // 活动分类设置
        $api->post('setActivitiesDict', ['uses' => 'ActivitiesController@setActivitiesDict', 'permission' => ['admin', '活动分类设置', '精彩活动', 'v1']])->name('platform.set_activities_dict');
        // 活动分类状态批量编辑
        $api->post('setDictStatus', ['uses' => 'ActivitiesController@setDictStatus', 'permission' => ['admin', '活动分类状态批量编辑', '精彩活动', 'v1']])->name('platform.set_dict_status');
        // 获取活动
        $api->get('getActivities', ['uses' => 'ActivitiesController@getActivities', 'permission' => ['admin', '获取活动', '精彩活动', 'v1']])->name('platform.get_activities');
        // 获取单个活动详情
        $api->get('getActivityDetail/{id}', ['uses' => 'ActivitiesController@getActivityDetail', 'permission' => ['admin', '获取单个活动详情', '精彩活动', 'v1']])->name('platform.get_activity_detail');
        // 设置活动所属分类
        $api->post('setDictids', ['uses' => 'ActivitiesController@setDictids', 'permission' => ['admin', '设置活动所属分类', '精彩活动', 'v1']])->name('platform.set_dictids');
        // 新增或编辑活动
        $api->post('setActivity', ['uses' => 'ActivitiesController@setActivity', 'permission' => ['admin', '新增或编辑活动', '精彩活动', 'v1']])->name('platform.set_activity');
        // 删除活动
        $api->delete('delActivity', ['uses' => 'ActivitiesController@delActivity', 'permission' => ['admin', '删除活动', '精彩活动', 'v1']])->name('platform.del_activity');
        // 上传文件
        $api->post('activityUpload', ['uses' => 'ActivitiesController@upload', 'permission' => ['admin', '上传文件', '精彩活动', 'v1']])->name('platform.activity_upload');
    });
});

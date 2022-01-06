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
$api->version('v1', function ($api) {

    //活动管理路由
    $api->group(['prefix' => 'news','middleware' => ['auth:admin', 'admin'],'namespace' => '\Modules\News\Http\Controllers'], function ($api) {
        //游戏公告路由
        $api->get('SystemNoticeList',['uses' => 'NewsBulletinController@system_notice_list', 'permission' => ['admin', '游戏公告列表', '游戏公告', 'v1']])
            ->name('news.system_notice_list');         // 游戏公告列表
        $api->get('getNoticeDetail/{id}',['uses' => 'NewsBulletinController@getNoticeDetail', 'permission' => ['admin', '游戏公告列表', '单个公告详情', 'v1']])
            ->name('news.get_notice_detail');         // 获取背景色
        $api->get('getBgColor',['uses' => 'NewsBulletinController@getBgColor']);
        $api->post('SystemNoticeAdd',['uses' => 'NewsBulletinController@system_notice_add', 'permission' => ['admin', '游戏公告新增', '游戏公告', 'v1']])
            ->name('news.system_notice_add');           // 游戏公告新增
        $api->post('SystemNoticeEdit',['uses' => 'NewsBulletinController@system_notice_edit', 'permission' => ['admin', '游戏公告编辑', '游戏公告', 'v1']])
            ->name('news.system_notice_edit');        // 游戏公告编辑
        $api->post('SystemNoticeDelete',['uses' => 'NewsBulletinController@system_notice_delete', 'permission' => ['admin', '游戏公告删除', '游戏公告', 'v1']])
            ->name('news.system_notice_delete');  // 游戏公告删除
        //精彩活动路由
        $api->get('AdsList',['uses' => 'NewsBulletinController@ads_list', 'permission' => ['admin', '精彩活动列表', '精彩活动', 'v1']])->name('news.ads_list');         // 广告管理列表
        $api->post('AdsSave',['uses' => 'NewsBulletinController@ads_save', 'permission' => ['admin', '精彩活动新增', '精彩活动', 'v1']])->name('news.ads_save');           // 广告管理新增
        $api->post('upload',['uses' => 'NewsBulletinController@upload']);        // 广告管理编辑
        //$api->post('upload',['uses' => 'NewsBulletinController@upload', 'permission' => ['admin', '活动图片上传', '精彩活动', 'v1']])->name('news.ads_upload');        // 广告管理编辑

	    $api->post('AdsDelete',['uses' => 'NewsBulletinController@ads_delete', 'permission' => ['admin', '精彩活动删除', '精彩活动', 'v1']])->name('news.ads_delete');  // 广告管理删除
        //系统消息路由
        $api->get('SystemMessageList',['uses' => 'NewsBulletinController@system_message_list', 'permission' => ['admin', '系统消息列表', '系统消息', 'v1']])
            ->name('news.system_message_list');              // 系统消息列表
        $api->post('SystemMessageAdd',['uses' => 'NewsBulletinController@system_message_add', 'permission' => ['admin', '系统消息新增', '系统消息', 'v1']])
            ->name('news.system_message_add');                // 系统消息新增
        $api->post('SystemMessageEdit/{message_id}',['uses' => 'NewsBulletinController@system_message_edit', 'permission' => ['admin', '系统消息编辑', '系统消息', 'v1']])
            ->name('news.system_message_edit');// 系统消息编辑
        $api->post('SystemMessageDelete',['uses' => 'NewsBulletinController@system_message_delete', 'permission' => ['admin', '系统消息删除', '系统消息', 'v1']])
            ->name('news.system_message_delete');       // 系统消息删除
        $api->get('KindRooms',['uses' => 'NewsBulletinController@kind_rooms', 'permission' => ['admin', '游戏房间信息', '系统消息', 'v1']])->name('news.kind_rooms');   // 所有游戏及对应房间信息
        $api->post('Status',['uses' => 'NewsBulletinController@status', 'permission' => ['admin', '系统消息状态设置', '系统消息', 'v1']])->name('news.status');         // 系统消息状态设置
        //站点配置-系统客服
        $api->post('CustomerService',['uses' => 'NewsBulletinController@system_customer_service', 'permission' => ['admin', '系统客服设置', '站点配置', 'v1']])
            ->name('news.system_customer_service');// 系统客服设置
        $api->get('CustomerServiceShow',['uses' => 'NewsBulletinController@customer_service_show', 'permission' => ['admin', '系统客服展示', '站点配置', 'v1']])
            ->name('news.customer_service_show');// 系统客服展示
       //站点配置-技术支持
        $api->post('TechnicalSupport',['uses' => 'NewsBulletinController@technical_support', 'permission' => ['admin', '技术支持设置', '站点配置', 'v1']])
            ->name('news.technical_support');// 技术支持设置
        $api->get('TechnicalSupportShow',['uses' => 'NewsBulletinController@technical_support_show', 'permission' => ['admin', '技术支持展示', '站点配置', 'v1']])
            ->name('news.technical_support_show');// 技术支持展示
        //IP白名单
        $api->get('WhiteIpList',['uses' => 'WhiteIpController@white_ip_list', 'permission' => ['admin', 'IP白名单列表', '白名单管理', 'v1']])
            ->name('news.white_ip_list');// ip白名单
        $api->post('WhiteIpSave', ['uses' => 'WhiteIpController@white_ip_save', 'permission' => ['admin', 'IP白名单保存', '白名单管理', 'v1']])
            ->name('news.white_ip_save');   //ip白名单保存
        $api->post('WhiteIpDelete', ['uses' => 'WhiteIpController@white_ip_delete', 'permission' => ['admin', 'IP白名单删除', '白名单管理', 'v1']])
            ->name('news.white_ip_delete');   //ip白名单删除
        //系统维护配置
        $api->get('ServerConfigShow',['uses' => 'ServerConfigController@server_config_show', 'permission' => ['admin', '系统维护展示', '服务器管理', 'v1']])
            ->name('news.server_config_show');  // 系统维护展示
        $api->post('ServerConfigSave', ['uses' => 'ServerConfigController@server_config_save', 'permission' => ['admin', '系统维护保存', '服务器管理', 'v1']])
            ->name('news.server_config_save');   //系统维护保存
        //站点配置-技术支持
        $api->post('YsfConfig',['uses' => 'NewsBulletinController@ysf_config', 'permission' => ['admin', '商城云闪付设置', '站点配置', 'v1']])
            ->name('news.ysf_config');// 商城云闪付设置
        $api->get('YsfConfigShow',['uses' => 'NewsBulletinController@ysf_config_show', 'permission' => ['admin', '商城云闪付设置展示', '站点配置', 'v1']])
            ->name('news.ysf_config_show');// 商城云闪付设置展示
    });
});

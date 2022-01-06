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

    //代理管理路由
    $api->group(['prefix' => 'agent', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Agent\Http\Controllers'], function ($api) {
        //代理列表
        $api->get('getList', ['uses' => 'AgentController@getList', 'permission' => ['admin', '代理列表', '代理管理', 'v1']])->name('agent.list');
        //代理订单列表
        $api->get('withdrawList', ['uses' => 'AgentWithdrawController@withdrawList', 'permission' => ['admin', '代理提现订单列表', '订单审核', 'v1']])->name('agent.withdraw_list');
        //代理审核通过
        $api->put('withdrawPass', ['uses' => 'AgentWithdrawController@withdrawPass', 'permission' => ['admin', '代理提现审核通过', '订单审核', 'v1']])->name('agent.withdraw_pass');
        //代理审核拒绝
        $api->put('withdrawRefuse', ['uses' => 'AgentWithdrawController@withdrawRefuse', 'permission' => ['admin', '代理提现审核拒绝', '订单审核', 'v1']])->name('agent.withdraw_refuse');
        //代理财务订单列表
        $api->get('financeList', ['uses' => 'AgentWithdrawController@financeList', 'permission' => ['admin', '代理提现订单列表', '财务审核', 'v1']])->name('agent.finance_list');
        //代理财务审核通过
        $api->put('financePass', ['uses' => 'AgentWithdrawController@financePass', 'permission' => ['admin', '代理财务审核通过', '财务审核', 'v1']])->name('agent.finance_pass');
        //代理财务审核拒绝
        $api->put('financeRefuse', ['uses' => 'AgentWithdrawController@financeRefuse', 'permission' => ['admin', '代理财务审核拒绝', '财务审核', 'v1']])->name('agent.finance_refuse');
        //代理周业绩列表
        $api->get('weekEnterpriseList', ['uses' => 'AgentController@weekEnterpriseList', 'permission' => ['admin', '代理周业绩列表', '代理管理', 'v1']])->name('agent.week_enterprise_list');
        //代理返利配置列表
        $api->get('rateConfigList', ['uses' => 'AgentRateConfigController@getList', 'permission' => ['admin', '代理返利配置列表', '代理管理', 'v1']])->name('agent.rate_config_list');
        //代理返利配置添加
        $api->post('rateConfigAdd', ['uses' => 'AgentRateConfigController@add', 'permission' => ['admin', '代理返利配置添加', '代理管理', 'v1']])->name('agent.rate_config_add');
        //代理返利配置编辑
        $api->put('rateConfigEdit', ['uses' => 'AgentRateConfigController@edit', 'permission' => ['admin', '代理返利配置编辑', '代理管理', 'v1']])->name('agent.rate_config_edit');
        //代理返利配置删除
        $api->delete('rateConfigDel', ['uses' => 'AgentRateConfigController@del', 'permission' => ['admin', '代理返利配置删除', '代理管理', 'v1']])->name('agent.rate_config_del');

    });

    //渠道路由组
    $api->group(['prefix' => 'channel', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Agent\Http\Controllers'], function ($api) {
        //渠道列表
        $api->get('channelList', ['uses' => 'ChannelController@channel_info', 'permission' => ['admin', '渠道列表', '渠道列表', 'v1']])->name('channel.channel_list');
        //子渠道列表
        $api->get('SubChannelList', ['uses' => 'ChannelController@sub_channel_info', 'permission' => ['admin', '子渠道列表', '渠道列表', 'v1']])->name('channel.sub_channel_list');
        //添加渠道
        $api->post('addChannel', ['uses' => 'ChannelController@add_channel', 'permission' => ['admin', '新增渠道', '渠道列表', 'v1']])->name('channel.add_channel');
        //编辑渠道
        $api->post('editChannel', ['uses' => 'ChannelController@edit_channel', 'permission' => ['admin', '编辑渠道', '渠道列表', 'v1']])->name('channel.edit_channel');
        //渠道禁用启用
        $api->put('channelStatus', ['uses' => 'ChannelController@channel_status', 'permission' => ['admin', '渠道禁用', '渠道列表', 'v1']])->name('channel.channel_status');
        //渠道订单审核列表
        $api->get('qudaoWithdrawList', ['uses' => 'ChannelWithdrawController@withdrawList', 'permission' => ['admin', '渠道订单', '订单审核', 'v1']])->name('channel.withdraw_list');
        //渠道订单审核通过
        $api->put('channelWithdrawPass', ['uses' => 'ChannelWithdrawController@withdrawPass', 'permission' => ['admin', '渠道订单审核通过', '订单审核', 'v1']])->name('channel.withdraw_pass');
        //渠道订单审核拒绝
        $api->put('channelWithdrawRefuse', ['uses' => 'ChannelWithdrawController@withdrawRefuse', 'permission' => ['admin', '渠道订单审核拒绝', '订单审核', 'v1']])->name('channel.withdraw_refuse');
        //渠道财务审核列表
        $api->get('channelFinanceList', ['uses' => 'ChannelWithdrawController@financeList', 'permission' => ['admin', '渠道订单', '财务审核', 'v1']])->name('channel.finance_list');
        //渠道财务审核通过
        $api->put('channelFinancePass', ['uses' => 'ChannelWithdrawController@financePass', 'permission' => ['admin', '渠道财务审核通过', '财务审核', 'v1']])->name('channel.finance_pass');
        //渠道财务审核拒绝
        $api->put('channelFinanceRefuse', ['uses' => 'ChannelWithdrawController@financeRefuse', 'permission' => ['admin', '渠道财务审核拒绝', '财务审核', 'v1']])->name('channel.finance_refuse');
        //用户详情
        $api->get('channelUserList', ['uses' => 'ChannelController@channel_user_list', 'permission' => ['admin', '用户详情', '渠道列表', 'v1']])->name('channel.channel_user_list');
        //添加子渠道
        $api->post('channelAddSubset', ['uses' => 'ChannelController@channel_add_subset', 'permission' => ['admin', '添加子渠道', '渠道列表', 'v1']])->name('channel.channel_add_subset');
        //编辑子渠道
        $api->post('channelEditSubset', ['uses' => 'ChannelController@channel_edit_subset', 'permission' => ['admin', '编辑子渠道', '渠道列表', 'v1']])->name('channel.channel_edit_subset');
        //导入子渠道
        $api->post('importChannelSubset', ['uses' => 'ChannelController@import_channel_subset', 'permission' => ['admin', '导入子渠道', '渠道列表', 'v1']])->name('channel.import_channel_subset');
        //官方渠道数据
        $api->get('officialChannel', ['uses' => 'ChannelController@official_channel', 'permission' => ['admin', '官方渠道', '渠道列表', 'v1']])->name('channel.official_channel');
    });

});

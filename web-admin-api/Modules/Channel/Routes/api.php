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
    $api->group(['prefix' => 'channel/auth', 'namespace' => '\Modules\Channel\Http\Controllers'], function ($api) {
        $api->post('login', 'ChannelAuthController@login');
        $api->post('logout', 'ChannelAuthController@logout');
        $api->post('refresh', 'ChannelAuthController@refresh');
        $api->post('me', 'ChannelAuthController@me');
    });
    $api->group(['prefix' => 'channel', 'middleware' => 'auth:channel', 'namespace' => '\Modules\Channel\Http\Controllers'], function ($api) {
        //渠道首页-推广人数（折线图）
        $api->get('channelSpreadSum', 'ChannelController@channel_spread_sum')->name('channel.channel_spread_sum')->middleware("channel.permission:father_channel|son_channel");
        //渠道首页-充值量（折线图）
        $api->get('channelRechargeSum', 'ChannelController@channel_recharge_sum')->name('channel.channel_recharge_sum')->middleware("channel.permission:father_channel|son_channel");
        //渠道首页-输赢（折线图）
        $api->get('channelTaxationSum', 'ChannelController@channel_taxation_sum')->name('channel.channel_taxation_sum')->middleware("channel.permission:father_channel|son_channel");
        //渠道首页-流水（折线图）
        $api->get('channelBetsSum', 'ChannelController@channel_bets_sum')->name('channel.channel_bets_sum')->middleware("channel.permission:father_channel|son_channel");
        //渠道首页-充值人数（折线图）
        $api->get('channelPeopleSum', 'ChannelController@channel_people_sum')->name('channel.channel_people_sum')->middleware("channel.permission:father_channel|son_channel");
        //渠道管理
        $api->get('nextChannelList', 'ChannelController@next_channel_list')->name('channel.next_channel_list')->middleware("channel.permission:father_channel");
       /* $api->post('channelAddSubset', 'ChannelController@channel_add_subset')->name('channel.channel_add_subset')->middleware("channel.permission:father_channel");//添加渠道
        $api->post('channelEditSubset', 'ChannelController@channel_edit_subset')->name('channel.channel_edit_subset')->middleware("channel.permission:father_channel");//渠道编辑*/
       //我的推广
        $api->get('mySpreadChannel', 'ChannelController@my_spread_channel')->name('channel.my_spread_channel')->middleware("channel.permission:father_channel|son_channel");
        //渠道记录
        $api->get('channelWithdrawList', 'ChannelController@channel_withdraw_list')->name('channel.channel_withdraw_list')->middleware("channel.permission:father_channel|son_channel");
        //渠道
        $api->post('channelWithdraw', 'ChannelController@channel_withdraw')->name('channel.channel_withdraw')->middleware("channel.permission:father_channel|son_channel");
        //渠道额度
        $api->post('channelWithdrawQuota', 'ChannelController@channel_withdraw_quota')->name('channel.channel_withdraw_quota')->middleware("channel.permission:father_channel|son_channel");
        //渠道返利方式和返利比例
        $api->get('channelReturnType', 'ChannelController@channel_return_type')->name('channel.channel_return_type')->middleware("channel.permission:father_channel|son_channel");
        //渠道查最新的账号信息
        $api->post('channelWithdrawAccount', 'ChannelController@channel_withdraw_account')->name('channel.channel_withdraw_account')->middleware("channel.permission:father_channel|son_channel");
        //渠道所有统计
        $api->get('channelCount', 'ChannelCountController@channelCount')->name('channel.channel_count')->middleware("channel.permission:father_channel|son_channel");//渠道个人推广统计
        $api->get('channelAllCount', 'ChannelCountController@channelAllCount')->name('channel.channel_all_count')->middleware("channel.permission:father_channel|son_channel");//渠道所有推广统计
    });

});

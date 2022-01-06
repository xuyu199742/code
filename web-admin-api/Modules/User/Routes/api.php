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
    $api->group(['prefix' => 'user','middleware' => ['auth:admin', 'admin'],'namespace' => '\Modules\User\Http\Controllers'], function ($api) {
        //用户列表
        $api->get('list',['uses' => 'UserController@getList', 'permission' => ['admin', '用户列表', '用户管理', 'v1']])->name('user.list');
        //用户详情
        $api->get('details',['uses' => 'UserController@getDetails', 'permission' => ['admin', '用户详情', '用户管理', 'v1']])->name('user.details');
        //用户列表——平台下分
        $api->get('refreshGold/{id}/{type}',['uses' => 'UserController@refreshGold', 'permission' => ['admin', '用户详情', '用户管理', 'v1']])->name('user.refresh_old');
        //用户登录状态设置
        $api->put('setNullity',['uses' => 'UserController@setNullity', 'permission' => ['admin', '用户登录状态设置', '用户管理', 'v1']])->name('user.set_nullity');
        //用户转账状态设置
        //$api->put('setTransfer',['uses' => 'UserController@setTransfer', 'permission' => ['admin', '用户转账状态设置', '用户管理', 'v1']])->name('user.set_transfer');
        //用户状态设置
        $api->put('setWithdraw',['uses' => 'UserController@setWithdraw', 'permission' => ['admin', '用户提现状态设置', '用户管理', 'v1']])->name('user.set_withdraw');
        //用户登录及状态设置
        $api->put('setNullityWithdraw',['uses' => 'UserController@setNullityWithdraw', 'permission' => ['admin', '用户登录及提现状态设置', '用户管理', 'v1']])->name('user.set_nullity_withdraw');
        //用户检测
        $api->get('check',['uses' => 'UserController@check', 'permission' => ['admin', '用户检测', '用户管理', 'v1']])->name('user.check');
        //用户日志
        $api->get('gameLog',['uses' => 'UserController@gameLog', 'permission' => ['admin', '用户日志', '用户管理', 'v1']])->name('user.game_log');
        //修改密码
        $api->put('resetPassword',['uses' => 'UserController@resetPassword', 'permission' => ['admin', '修改密码', '用户管理', 'v1']])->name('user.reset_password');
        //刷新账户余额
        $api->get('gameScore',['uses' => 'UserController@gameScore', 'permission' => ['admin', '刷新账户余额', '用户管理', 'v1']])->name('user.game_score');
        //刷新推广余额
        $api->get('promotionScore',['uses' => 'UserController@promotionScore', 'permission' => ['admin', '刷新推广余额', '用户管理', 'v1']])->name('user.promotion_score');
        //金币记录（游戏内）
        $api->get('goldList',['uses' => 'GoldController@getList', 'permission' => ['admin', '金币记录', '用户管理', 'v1']])->name('user.gold_list');
        //金币赠送
        $api->post('goldGive',['uses' => 'GoldController@give', 'permission' => ['admin', '金币赠送', '用户管理', 'v1']])->name('user.gold_give');
        //金币流水（游戏外）
        $api->get('goldGeneralWater',['uses' => 'GoldController@generalWater', 'permission' => ['admin', '金币流水', '用户管理', 'v1']])->name('user.gold_general_water');
        //金币流水（游戏）
        //$api->get('goldGameWater',['uses' => 'GoldController@gameWater', 'permission' => ['admin', '金币流水（游戏）', '用户管理', 'v1']])->name('user.gold_game_water');
        //转账记录
        $api->get('transferLog',['uses' => 'GoldController@transferLog', 'permission' => ['admin', '转账记录', '用户管理', 'v1']])->name('user.transfer_log');
        //游戏联动房间
        $api->get('gameLinkRoom',['uses' => 'GameController@gameLinkRoom']);
        //当前在线人数
        $api->get('onlineCount',['uses' => 'OnLineController@count']);
        //在线用户
        $api->get('onlineList',['uses' => 'OnLineController@getList', 'permission' => ['admin', '在线用户', '用户管理', 'v1']])->name('user.online_list');
        //清除卡线
        $api->delete('onlineClearForqkftod',['uses' => 'OnLineController@clearForqkftod', 'permission' => ['admin', '清除卡线', '用户管理', 'v1']])->name('user.online_delete_forqkftod');
        //游戏进出
        $api->get('inoutLog',['uses' => 'GameInOutController@log', 'permission' => ['admin', '游戏进出', '用户管理', 'v1']])->name('user.game_inout_log');
        //充值记录
        $api->get('payList',['uses' => 'PayController@getList', 'permission' => ['admin', '充值记录', '用户管理', 'v1']])->name('user.pay_list');
        //记录
        $api->get('withdrawList',['uses' => 'WithdrawController@getList', 'permission' => ['admin', '提现记录', '用户管理', 'v1']])->name('user.withdraw_list');
        //银行记录
        $api->get('insureList',['uses' => 'InsureController@getList', 'permission' => ['admin', '银行记录', '用户管理', 'v1']])->name('user.insure_list');
        //用户绑定渠道时可选的所有渠道
        //$api->get('channelList',['uses' => 'UserController@channelList', 'permission' => ['admin', '可选渠道', '用户管理', 'v1']])->name('user.channel_list');
        $api->get('channelList',['uses' => 'UserController@channelList']);
        //用户绑定渠道
        $api->post('channelBind',['uses' => 'UserController@channelBind', 'permission' => ['admin', '绑定渠道', '用户管理', 'v1']])->name('user.channel_bind');
        //重置用户银行密码
        $api->post('resetBackPass',['uses' => 'UserController@resetBackPass', 'permission' => ['admin', '重置银行密码', '用户管理', 'v1']])->name('user.reset_back_pass');
        //系统发送邮件列表
        $api->get('sendEmailList',['uses' => 'EmailController@sendEmailList', 'permission' => ['admin', '发送邮件列表', '邮件系统', 'v1']])->name('user.send_email_list');
        //用户发送邮件列表(收件箱列表)
        $api->get('userEmailList',['uses' => 'EmailController@userEmailList', 'permission' => ['admin', '收件箱', '邮件系统', 'v1']])->name('user.user_email_list');
        //发送邮件
        $api->post('sendEmail', ['uses' => 'EmailController@sendEmail', 'permission' => ['admin', '发送邮件', '邮件系统', 'v1']])->name('user.send_email');
        $api->get('logs', ['uses' => 'UserController@logs', 'permission' => ['admin', '批量封号', '用户管理', 'v1']])->name('user.logs');
        //------vip配置------
        //VIP列表
        $api->get('membersList', ['uses' => 'MembersController@getList', 'permission' => ['admin', 'VIP列表','玩家VIP', 'v1']])->name('members.list');
        //获取VIP关联活动配置
        $api->get('getRelationConfig', ['uses' => 'MembersController@getRelationConfig', 'permission' => ['admin', '获取VIP关联活动配置','玩家VIP', 'v1']])->name('members.get_relation_config');
        //修改VIP关联活动配置
        $api->post('setRelationConfig', ['uses' => 'MembersController@setRelationConfig', 'permission' => ['admin', '修改VIP关联活动配置', '玩家VIP', 'v1']])->name('members.set_relation_config');
        //获取VIP信息
        $api->get('getVIPInfo', ['uses' => 'MembersController@getVIPInfo', 'permission' => ['admin', 'VIP信息', '玩家VIP', 'v1']])->name('members.vip_info');
        //新增VIP
        $api->post('addVIP', ['uses' => 'MembersController@addVIP', 'permission' => ['admin', '新增VIP','玩家VIP', 'v1']])->name('members.add_vip');
        //修改VIP
        $api->post('editVIP', ['uses' => 'MembersController@editVIP', 'permission' => ['admin', '修改VIP','玩家VIP','v1']])->name('members.edit_vip');
        //获取该等级会员奖励详情
        $api->get('getMemberHandsel', ['uses' => 'MembersController@getMemberHandsel', 'permission' => ['admin', '奖励详情','玩家VIP', 'v1']])->name('members.Handsel');
        //获取福利类型选项
        $api->get('getHandselType', ['uses' => 'MembersController@getHandselType', 'permission' => ['admin', '福利类型', '玩家VIP','v1']])->name('members.Handsel_type');
        //获取VIP奖励日志列表
        $api->get('getHandselLogs', ['uses' => 'MembersController@getHandselLogs', 'permission' => ['admin', 'VIP奖励日志列表', '玩家VIP','v1']])->name('members.Handsel_logs');
        //修改所有VIP状态
        $api->post('setVipStatus', ['uses' => 'MembersController@setVipStatus', 'permission' => ['admin', '修改所有VIP状态', '玩家VIP', 'v1']])->name('members.set_vip_status');
        //修复所有用户VIP等级
        $api->get('repairLevel', ['uses' => 'MembersController@repairLevel', 'permission' => ['admin', '修复所有用户VIP等级', '玩家VIP', 'v1']])->name('members.repair_level');
        //绑定银行卡
        $api->post('bindCard', ['uses' => 'WithdrawController@bindCard', 'permission' => ['admin', '绑定银行卡', '用户管理', 'v1']])->name('user.bind_card');
        //获取机器人VIP等级配置列表
        $api->get('androidVipList', ['uses' => 'MembersController@androidVipList', 'permission' => ['admin', '机器人VIP等级配置列表', '玩家VIP', 'v1']])->name('members.android_vip_list');
        //新增或编辑机器人VIP等级配置
        $api->post('androidVipEdit', ['uses' => 'MembersController@androidVipEdit', 'permission' => ['admin', '新增或编辑机器人VIP等级配置', '玩家VIP', 'v1']])->name('members.android_vip_edit');
        //删除机器人VIP等级配置
        $api->post('androidVipDel', ['uses' => 'MembersController@androidVipDel', 'permission' => ['admin', '删除机器人VIP等级配置', '玩家VIP', 'v1']])->name('members.android_vip_del');
        //用户稽核列表
        $api->get('auditBetList', ['uses' => 'UserController@auditBetList', 'permission' => ['admin', '用户稽核列表', '用户稽核', 'v1']])->name('user.auditBet_list');
        //增加或减少稽核打码
        $api->post('auditBetEdit', ['uses' => 'UserController@auditBetEdit', 'permission' => ['admin', '增加或减少稽核打码', '用户稽核', 'v1']])->name('user.auditBet_edit');
        //配置活动稽核打码倍数
        $api->post('auditBetTake', ['uses' => 'UserController@auditBetTake', 'permission' => ['admin', '配置活动稽核打码倍数', '用户稽核', 'v1']])->name('user.auditBet_take');
        //用户层级列表
        $api->get('getUserLevelList', ['uses' => 'UserLevelController@list', 'permission' => ['admin', '用户层级列表', '用户层级', 'v1']])->name('user_level.list');
        //修改用户层级
        $api->put('updatetUserLevel/{id}', ['uses' => 'UserLevelController@update', 'permission' => ['admin', '修改用户层级', '用户层级', 'v1']])->name('user_level.update');
        //批量上分
        $api->post('batchAddScore', ['uses' => 'GoldController@batchAddScore', 'permission' => ['admin', '批量上分', '用户管理', 'v1']])->name('user.batch_add_score');
    });

});

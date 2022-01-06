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
    $api->group(['prefix' => 'system', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\System\Http\Controllers'], function ($api) {

        //短信配置列表
        //$api->get('smsConfigList', ['uses' => 'SmsConfigController@getList', 'permission' => ['admin', '短信配置列表', '系统管理', 'v1']])->name('system.sms_config_list');
        //短信配置添加
        //$api->post('smsConfigAdd', ['uses' => 'SmsConfigController@add', 'permission' => ['admin', '短信配置添加', '系统管理', 'v1']])->name('system.sms_config_add');
        //短信配置修改
        //$api->put('smsConfigEdit', ['uses' => 'SmsConfigController@edit', 'permission' => ['admin', '短信配置修改', '系统管理', 'v1']])->name('system.sms_config_edit');
        //短信配置删除
        //$api->delete('smsConfigDel', ['uses' => 'SmsConfigController@del', 'permission' => ['admin', '短信配置删除', '系统管理', 'v1']])->name('system.sms_config_del');
        //短信配置更新缓存
        //$api->put('smsConfigCache', ['uses' => 'SmsConfigController@cache', 'permission' => ['admin', '短信配置更新缓存', '系统管理', 'v1']])->name('system.sms_config_cache');

        //获取系统配置
        $api->get('settingList', ['uses' => 'SystemSettingController@getList', 'permission' => ['admin', '获取系统配置', '系统管理', 'v1']])->name('system.setting_list');
        //更新系统配置
        $api->put('settingEdit', ['uses' => 'SystemSettingController@edit', 'permission' => ['admin', '更新系统配置', '系统管理', 'v1']])->name('system.setting_edit');
        //获取首充设置
        $api->get('firstPayList', ['uses' => 'SystemSettingController@getList', 'permission' => ['admin', '获取首充设置', '系统管理', 'v1']])->name('system.first_pay_list');
        //编辑首充设置
        $api->put('firstPayEdit', ['uses' => 'SystemSettingController@edit', 'permission' => ['admin', '编辑首充设置', '系统管理', 'v1']])->name('system.first_pay_edit');
        //获取内部，外部充值百分比配置
        $api->get('rechargePercentageList', ['uses' => 'SystemSettingController@getList', 'permission' => ['admin', '获取内,外部充值百分比', '系统管理', 'v1']])->name('system.recharge_percentage_list');
        //编辑内部，外部充值百分比配置
        $api->put('rechargePercentageEdit', ['uses' => 'SystemSettingController@edit', 'permission' => ['admin', '编辑内,外部充值百分比', '系统管理', 'v1']])->name('system.recharge_percentage_edit');
        //注册ip设置
        $api->post('updateRegisterIp', ['uses' => 'SystemSettingController@updateRegisterIp', 'permission' => ['admin', '注册ip设置', '系统全局设置', 'v1']])->name('system.update_register_ip');
        //注册信息设置
        $api->post('updateRegisterInfo', ['uses' => 'SystemSettingController@updateRegisterInfo', 'permission' => ['admin', '注册信息设置', '系统全局设置', 'v1']])->name('system.update_register_info');
        //注册信息列表
        $api->get('getRegisterInfo', ['uses' => 'SystemSettingController@getRegisterInfo', 'permission' => ['admin', '注册信息列表', '系统全局设置', 'v1']])->name('system.get_register_info');
        //更新系统配置缓存
        //$api->put('settingCache', ['uses' => 'SystemSettingController@cache', 'permission' => ['admin', '更新系统配置缓存', '系统管理', 'v1']])->name('system.setting_cache');

        //获取取款税率
        //$api->get('statusInfoShow/{key}', ['uses' => 'SystemStatusInfoController@show', 'permission' => ['admin', '获取取款税率', '系统管理', 'v1']])->name('system.statusinfo_show');
        //修改取款税率
        $api->match(['get','put'],'statusInfoEdit/{key}', ['uses' => 'SystemStatusInfoController@edit', 'permission' => ['admin', '修改取款税率', '系统管理', 'v1']])->name('system.statusinfo_edit');
        //代理结算方式配置
        $api->match(['get','put'],'agentMethods/{key}', ['uses' => 'SystemStatusInfoController@edit', 'permission' => ['admin', '代理结算方式配置', '代理管理', 'v1']])->name('system.agent_methods_edit');
        //体验场入场积分
        $api->match(['get','put'],'experiencePoints/{key}', ['uses' => 'SystemStatusInfoController@experiencePoints', 'permission' => ['admin', '体验场入场积分', '系统管理', 'v1']])->name('system.experience_points');
        //体验场体验时长
        $api->match(['get','put'],'experienceTime/{key}', ['uses' => 'SystemStatusInfoController@experienceTime', 'permission' => ['admin', '体验场体验时长', '系统管理', 'v1']])->name('system.experience_time');

        //签到配置列表
        $api->get('signinSettingList', ['uses' => 'SigninSettingController@getList', 'permission' => ['admin', '签到配置列表', '签到管理', 'v1']])->name('system.signin_setting_list');
        //签到配置添加
        //$api->post('signinSettingAdd', ['uses' => 'SigninSettingController@add', 'permission' => ['admin', '签到配置添加', '签到管理', 'v1']])->name('system.signin_setting_add');
        //签到配置修改
        $api->put('signinSettingEdit/{sign_id}', ['uses' => 'SigninSettingController@edit', 'permission' => ['admin', '签到配置修改', '签到管理', 'v1']])->name('system.signin_setting_edit');
        //签到配置删除
        //$api->delete('signinSettingDel/{sign_id}', ['uses' => 'SigninSettingController@del', 'permission' => ['admin', '签到配置删除', '签到管理', 'v1']])->name('system.signin_setting_del');

        //签到礼包配置下拉选择
        $api->get('signinGiftAll', ['uses' => 'SigninGiftSettingController@getAll', 'permission' => ['admin', '礼包配置下拉选择', '签到管理', 'v1']])->name('system.signin_gift_all');
        //签到礼包配置列表
        //$api->get('signinGiftList', ['uses' => 'SigninGiftSettingController@getList', 'permission' => ['admin', '礼包配置列表', '签到管理', 'v1']])->name('system.signin_gift_list');
        //签到礼包配置添加
        //$api->post('signinGiftAdd', ['uses' => 'SigninGiftSettingController@add', 'permission' => ['admin', '礼包配置添加', '签到管理', 'v1']])->name('system.signin_gift_add');
        //签到礼包配置修改
        //$api->put('signinGiftEdit/{package_id}', ['uses' => 'SigninGiftSettingController@edit', 'permission' => ['admin', '礼包配置修改', '签到管理', 'v1']])->name('system.signin_gift_edit');
        //签到礼包配置删除
        //$api->delete('signinGiftDel/{package_id}', ['uses' => 'SigninGiftSettingController@del', 'permission' => ['admin', '礼包配置删除', '签到管理', 'v1']])->name('system.signin_gift_del');

        //签到物品配置列表
        $api->get('signinGoodsList', ['uses' => 'SigninGoodsSettingController@getList', 'permission' => ['admin', '物品配置列表', '签到管理', 'v1']])->name('system.signin_goods_list');
        //签到物品配置添加
        //$api->post('signinGoodsAdd', ['uses' => 'SigninGoodsSettingController@add', 'permission' => ['admin', '物品配置添加', '签到管理', 'v1']])->name('system.signin_goods_add');
        //签到物品配置修改
        $api->put('signinGoodsEdit/{goods_id}', ['uses' => 'SigninGoodsSettingController@edit', 'permission' => ['admin', '物品配置修改', '签到管理', 'v1']])->name('system.signin_goods_edit');
        //签到物品配置删除
        //$api->delete('signinGoodsDel/{goods_id}', ['uses' => 'SigninGoodsSettingController@del', 'permission' => ['admin', '物品配置删除', '签到管理', 'v1']])->name('system.signin_goods_del');
        //签到记录
        //$api->get('signinLogList', ['uses' => 'SigninLogController@getList', 'permission' => ['admin', '签到记录', '签到管理', 'v1']])->name('system.signin_log_list');
        //用户分享列表
        $api->get('userShareList', ['uses' => 'SigninGoodsSettingController@user_share_show', 'permission' => ['admin', '用户分享列表', '签到管理', 'v1']])->name('system.user_share_show');
        //用户分享配置
        $api->post('userShareConfig', ['uses' => 'SigninGoodsSettingController@user_share_config', 'permission' => ['admin', '用户分享配置', '签到管理', 'v1']])->name('system.user_share_config');
        //用户分享配置-上传图片
        $api->post('userShareUpload', ['uses' => 'SigninGoodsSettingController@user_share_upload']);
        //$api->post('userShareUpload', ['uses' => 'SigninGoodsSettingController@user_share_upload'])->name('system.user_share_upload');
        //包列表
        $api->get('/packages', ['uses' => 'PackageController@index', 'permission' => ['admin', '包列表', '包管理', 'v1']])->name('system.packages');
        //添加包
        $api->post('/package/create', ['uses' => 'PackageController@store', 'permission' => ['admin', '添加包', '包管理', 'v1']])->name('system.package.create');
        //修改包
        $api->post('/package/update', ['uses' => 'PackageController@store', 'permission' => ['admin', '修改包', '包管理', 'v1']])->name('system.package.update');
        //删除包
        $api->delete('/package/delete/{id}', ['uses' => 'PackageController@destroy', 'permission' => ['admin', '删除包', '包管理', 'v1']])->name('system.package.destroy');
        //查看包信息
        $api->get('/package/{id}', ['uses' => 'PackageController@show', 'permission' => ['admin', '查看包信息', '包管理', 'v1']])->name('system.package.show');

        //获取所有支付平台信息
        $api->get('/platforms', ['uses' => 'PaymentSettingController@platforms', 'permission' => ['admin', '支付平台列表', '四方充值管理', 'v1']])->name('system.payment.platforms');
        //获取单个支付平台配置信息
        $api->get('/platform/{key}', ['uses' => 'PaymentSettingController@platform', 'permission' => ['admin', '平台配置', '四方充值管理', 'v1']])->name('system.payment.platform.key');
        //保存平台配置
        $api->post('/save/platform', ['uses' => 'PaymentSettingController@savePlatform', 'permission' => ['admin', '保存平台配置', '四方充值管理', 'v1']])->name('system.payment.savePlatform');

        //清除支付缓存
        $api->get('/platform/clear/cache', ['uses' => 'PaymentSettingController@clearCache', 'permission' => ['admin', '清除支付缓存', '四方充值管理', 'v1']])->name('system.payment.clear.cache');

        //充值方式设置列表
        $api->get('paymentWaysList', ['uses' => 'PaymentWaysController@getRechargeWaysList', 'permission' => ['admin', '充值方式列表', '充值方式管理', 'v1']])->name('system.payment.ways.list');
        //官方充值通道列表
        $api->get('officialWaysList', ['uses' => 'PaymentWaysController@getOfficialWaysList', 'permission' => ['admin', '官方充值通道列表', '充值方式管理', 'v1']])->name('system.official.ways.list');
        //充值方式修改
        $api->post('paymentWaysSave', ['uses' => 'PaymentWaysController@getRechargeWaysSave', 'permission' => ['admin', '充值方式保存', '充值方式管理', 'v1']])->name('system.payment.ways.save');
        //获取充值通道配置
        $api->get('/paymentPassWaysList', ['uses' => 'PaymentWaysController@getRechargePassWaysList', 'permission' => ['admin', '获取充值通道配置', '充值方式管理', 'v1']])->name('system.payment.passways.list');
        //充值通道配置修改
        $api->post('paymentPassWaysSave', ['uses' => 'PaymentWaysController@getRechargePassWaysSave', 'permission' => ['admin', '充值通道配置保存', '充值方式管理', 'v1']])->name('system.payment.passways.save');
        //四方通道配置删除
        $api->delete('paymentPassWaysdel', ['uses' => 'PaymentWaysController@getRechargePassWaysDel', 'permission' => ['admin', '四方通道配置删除', '充值方式管理', 'v1']])->name('system.payment.passways.del');


        //获取所有支付类型
        $api->get('/tabs/{id}', ['uses' => 'PaymentSettingController@tabs', 'permission' => ['admin', '获取所有支付类型', '四方充值管理', 'v1']])->name('system.payment.tabs');
        //获取所有支付通道
        $api->post('/channels', ['uses' => 'PaymentSettingController@channels', 'permission' => ['admin', '获取所有支付通道', '四方充值管理', 'v1']])->name('system.payment.channels');
        //获取单个支付通道配置
        $api->get('/channel/{id}', ['uses' => 'PaymentSettingController@channel', 'permission' => ['admin', '获取单个支付通道配置', '四方充值管理', 'v1']])->name('system.payment.channel');
        //获取select支付通道
        $api->post('/select/channels', ['uses' => 'PaymentSettingController@selectChannels', 'permission' => ['admin', '获取支付通道', '四方充值管理', 'v1']])->name('system.payment.select.channels');
        //保存支付通道
        $api->post('/save/channel', ['uses' => 'PaymentSettingController@saveChannel', 'permission' => ['admin', '保存支付通道', '四方充值管理', 'v1']])->name('system.payment.save.channel');


        //官方充值平台设置
        $api->get('/official/wechat', ['uses' => 'OfficialPaymentController@wechat', 'permission' => ['admin', '官方微信列表', '官方充值管理', 'v1']])->name('system.official.wechat');
        $api->get('/official/alipay', ['uses' => 'OfficialPaymentController@alipay', 'permission' => ['admin', '官方支付宝列表', '官方充值管理', 'v1']])->name('system.official.alipay');
        $api->get('/official/union', ['uses' => 'OfficialPaymentController@union', 'permission' => ['admin', '官方银联列表', '官方充值管理', 'v1']])->name('system.official.union');
        $api->get('/official/agent', ['uses' => 'OfficialPaymentController@agent', 'permission' => ['admin', '官方代理列表', '官方充值管理', 'v1']])->name('system.official.agent');
        $api->get('/official/bank/info', ['uses' => 'OfficialPaymentController@bank', 'permission' => ['admin', '官方银联下拉信息', '官方充值管理', 'v1']])->name('system.official.bank.info');
        $api->get('/official/number/type', ['uses' => 'OfficialPaymentController@numberType', 'permission' => ['admin', '官方代理下拉列表', '官方充值管理', 'v1']])->name('system.official.number.type');
        $api->post('/official/saveWechat', ['uses' => 'OfficialPaymentController@saveWechat', 'permission' => ['admin', '官方微信保存', '官方充值管理', 'v1']])->name('system.official.save.wechat');
        $api->post('/official/saveAlipay', ['uses' => 'OfficialPaymentController@saveAlipay', 'permission' => ['admin', '官方支付宝保存', '官方充值管理', 'v1']])->name('system.official.save.alipay');
        $api->post('/official/saveUnion', ['uses' => 'OfficialPaymentController@saveUnion', 'permission' => ['admin', '官方银联保存', '官方充值管理', 'v1']])->name('system.official.save.union');
        $api->post('/official/saveAgent', ['uses' => 'OfficialPaymentController@saveAgent', 'permission' => ['admin', '官方代理保存', '官方充值管理', 'v1']])->name('system.official.save.agent');


        $api->post('/official/change/status/on', ['uses' => 'OfficialPaymentController@changeStatusOn', 'permission' => ['admin', '启用充值', '官方充值管理', 'v1']])->name('system.official.change.status.on');
        $api->post('/official/change/status/off', ['uses' => 'OfficialPaymentController@changeStatusOff', 'permission' => ['admin', '禁用充值', '官方充值管理', 'v1']])->name('system.official.change.status.off');
        $api->delete('/official/changeStatusDel', ['uses' => 'OfficialPaymentController@changeStatusDel', 'permission' => ['admin', '官方充值删除', '官方充值管理', 'v1']])->name('system.official.change.status.off');
        $api->post('/official/upload', ['uses' => 'OfficialPaymentController@upload']);
        //$api->post('/official/upload', ['uses' => 'OfficialPaymentController@upload', 'permission' => ['admin', '上传图片', '官方充值管理', 'v1']])->name('system.official.upload');

        // 日志路由
        $api->get('ErrorLogs', ['uses' => 'LogsController@error_logs', 'permission' => ['admin', '错误日志', '日志管理', 'v1']])->name('system.error_logs');
        $api->get('LoginLogs', ['uses' => 'LogsController@login_logs', 'permission' => ['admin', '登录日志', '日志管理', 'v1']])->name('system.login_logs');
        $api->get('OrderLogs', ['uses' => 'LogsController@order_logs', 'permission' => ['admin', '订单日志', '日志管理', 'v1']])->name('system.order_logs');
        $api->get('SmsLogs', ['uses' => 'LogsController@sms_logs', 'permission' => ['admin', '短信日志', '日志管理', 'v1']])->name('system.sms_logs');
        $api->get('SystemLogs', ['uses' => 'LogsController@system_logs', 'permission' => ['admin', '系统日志', '日志管理', 'v1']])->name('system.system_logs');
        $api->get('UserLogonLogs', ['uses' => 'LogsController@user_logon_logs', 'permission' => ['admin', '自动稽核查询', '日志管理', 'v1']])->name('system.user_logon_logs');
        $api->get('gameControlLog', ['uses' => 'LogsController@gameControlLog', 'permission' => ['admin', '操作日志', '日志管理', 'v1']])->name('system.game_control_log');

        //vip商人路由
        $api->get('VipTraderList', ['uses' => 'VipBusinessmanController@vip_trader_list', 'permission' => ['admin', 'vip商人列表', 'vip商人管理', 'v1']])->name('system.vip_trader_list');
        $api->get('AdminUsersList', ['uses' => 'VipBusinessmanController@admin_users_list', 'permission' => ['admin', '后台管理员列表', 'vip商人管理', 'v1']])->name('system.admin_users_list');
        $api->post('VipTraderSave', ['uses' => 'VipBusinessmanController@vip_trader_save', 'permission' => ['admin', 'vip商人保存', 'vip商人管理', 'v1']])->name('system.vip_trader_save');
        $api->post('VipTraderStatus', ['uses' => 'VipBusinessmanController@vip_trader_status', 'permission' => ['admin', 'vip商人禁用', 'vip商人管理', 'v1']])->name('system.vip_trader_status');
        $api->post('Upload', ['uses' => 'VipBusinessmanController@upload']);
        //$api->post('Upload', ['uses' => 'VipBusinessmanController@upload', 'permission' => ['admin', '头像上传', 'vip商人管理', 'v1']])->name('system.upload');

        //h5轮播网址
        $api->get('CarouselWebsiteList', ['uses' => 'CarouselWebsiteController@getList', 'permission' => ['admin', '轮播网址列表', '轮播网址', 'v1']])->name('system.website_list');
        $api->post('CarouselWebsiteAdd', ['uses' => 'CarouselWebsiteController@add', 'permission' => ['admin', '轮播网址添加', '轮播网址', 'v1']])->name('system.website_add');
        $api->put('CarouselWebsiteEdit/{id}', ['uses' => 'CarouselWebsiteController@edit', 'permission' => ['admin', '轮播网址编辑', '轮播网址', 'v1']])->name('system.website_edit');
        $api->delete('CarouselWebsiteDel/{id}', ['uses' => 'CarouselWebsiteController@del', 'permission' => ['admin', '轮播网址删除', '轮播网址', 'v1']])->name('system.website_del');
        //设置轮播时长
        $api->match(['get', 'post'],'CarouselTimeSet', ['uses' => 'CarouselWebsiteController@setTimes', 'permission' => ['admin', '轮播网址时长设置', '轮播网址', 'v1']])->name('system.set_times');
        //设置H5广告轮播时长
        $api->match(['get', 'post'],'CarouselAfficheTimeSet', ['uses' => 'CarouselAfficheController@carousel_affiche_setTimes', 'permission' => ['admin', '轮播广告时长设置', '轮播广告', 'v1']])->name('system.carousel_affiche_setTimes');
        //h5轮播广告
        $api->get('CarouselAfficheList', ['uses' => 'CarouselAfficheController@getList', 'permission' => ['admin', '轮播广告列表', '轮播广告', 'v1']])->name('system.affiche_list');
        $api->post('CarouselAfficheAdd', ['uses' => 'CarouselAfficheController@add', 'permission' => ['admin', '轮播广告添加', '轮播活动', 'v1']])->name('system.affiche_add');
        $api->put('CarouselAfficheEdit/{id}', ['uses' => 'CarouselAfficheController@edit', 'permission' => ['admin', '轮播广告编辑', '轮播广告', 'v1']])->name('system.affiche_edit');
        $api->delete('CarouselAfficheDel/{id}', ['uses' => 'CarouselAfficheController@del', 'permission' => ['admin', '轮播广告删除', '轮播广告', 'v1']])->name('system.affiche_del');
        //广告图片上传
        $api->post('uploadImage', ['uses' => 'UploadsController@uploadImage']);
        //$api->post('uploadImage', ['uses' => 'UploadsController@uploadImage', 'permission' => ['admin', '广告图片上传', '轮播广告', 'v1']])->name('system.upload_image');

        //导出excel表格
        //1、金币流水—游戏内金币记录
        /*$api->get('exportGetList', ['uses' => 'ExportController@export_getList', 'permission' => ['admin', '游戏内金币记录导出', '导出金币流水', 'v1']])->name('system.export_getList');
        //2、金币流水—游戏外金币记录
        $api->get('exportGeneralWater', ['uses' => 'ExportController@export_generalWater', 'permission' => ['admin', '游戏外金币记录导出', '导出金币流水l', 'v1']])
            ->name('system.export_generalWater');
        //3、导出充值订单
        $api->get('exportPayments', ['uses' => 'ExportController@export_payments', 'permission' => ['admin', '充值订单导出', '导出充值订单', 'v1']])
            ->name('system.export_payments');
        //4、导出内部充值订单
        $api->get('exportOfficial', ['uses' => 'ExportController@export_official', 'permission' => ['admin', '充值内部订单导出', '导出充值订单', 'v1']])
            ->name('system.export_official');
        //5、导出vip商人订单
        $api->get('exportVip', ['uses' => 'ExportController@export_vip', 'permission' => ['admin', 'vip商人订单导出', '导出充值订单', 'v1']])
            ->name('system.export_vip');
        //6、导出订单审核用户订单
        $api->get('exportWithdrawals', ['uses' => 'ExportController@export_withdrawals', 'permission' => ['admin', '订单审核用户订单', '导出订单', 'v1']])
            ->name('system.export_withdrawals');
        //7、导出订单审核代理订单
        $api->get('exportAgentWithdraw', ['uses' => 'ExportController@export_agentWithdraw', 'permission' => ['admin', '订单审核代理订单', '导出订单', 'v1']])
            ->name('system.export_agentWithdraw');
        //8、导出订单审核渠道订单
        $api->get('exportChannelWithdraw', ['uses' => 'ExportController@export_channelWithdraw', 'permission' => ['admin', '订单审核渠道订单', '导出订单', 'v1']])
            ->name('system.export_channelWithdraw');
        //9、导出财务审核用户订单
        $api->get('exportFinanceList', ['uses' => 'ExportController@export_financeList', 'permission' => ['admin', '财务审核用户订单', '导出订单', 'v1']])
            ->name('system.export_financeList');
        //10、导出财务审核代理订单
        $api->get('exportAgentFinance', ['uses' => 'ExportController@export_agentFinance', 'permission' => ['admin', '财务审核代理订单', '导出订单', 'v1']])
            ->name('system.export_agentFinance');
        //11、导出财务审核渠道订单
        $api->get('exportChannelFinance', ['uses' => 'ExportController@export_channelFinance', 'permission' => ['admin', '财务审核渠道订单', '导出订单', 'v1']])
            ->name('system.export_channelFinance');
        */
        //报表系统
        /*$api->get('reportForms', ['uses' => 'ReportFormsController@getInfo', 'permission' => ['admin', '报表系统', '报表信息', 'v1']])->name('system.report_forms');
        $api->get('payReportDetails', ['uses' => 'ReportFormsController@payReportDetails', 'permission' => ['admin', '报表二级详情', '报表信息', 'v1']])->name('system.pay_report_details');*/

        //首页优化 - 首充
        $api->get('homePage/firstRecharge', ['uses' => 'AnalysisController@firstRecharge']);
        //数据分析 - 充值
        $api->get('analysis/rechargeWithdrawal', ['uses' => 'AnalysisController@rechargeWithdrawal', 'permission' => ['admin', '充值提现', '数据分析', 'v1']])->name('analysis.recharge_withdrawal');
        //数据分析 - 优惠（活动礼金）
        $api->get('analysis/cashGift', ['uses' => 'AnalysisController@cashGift', 'permission' => ['admin', '优惠', '数据分析', 'v1']])->name('analysis.cash_gift');
        //数据分析 - 下注分析
        $api->get('analysis/jettonAnalysis', ['uses' => 'AnalysisController@jettonAnalysis', 'permission' => ['admin', '下注分析', '数据分析', 'v1']])->name('analysis.jetton_analysis');
        //数据分析 - 下注明细
        $api->get('analysis/jettonDetails', ['uses' => 'AnalysisController@jettonDetails', 'permission' => ['admin', '下注明细', '数据分析', 'v1']])->name('analysis.jetton_details');
        //数据分析 - 投注人数
        $api->get('analysis/bettingReport', ['uses' => 'AnalysisController@bettingReport', 'permission' => ['admin', '投注人数', '数据分析', 'v1']])->name('analysis.betting_report');
        //点控设置列表
        $api->get('pointControlList', ['uses' => 'PointControlController@getList', 'permission' => ['admin', '点控设置列表', '点控设置', 'v1']])->name('system.point_control_list');
        //点控设置保存
        $api->post('pointControlAdd', ['uses' => 'PointControlController@pointControlAdd', 'permission' => ['admin', '点控设置保存', '点控设置', 'v1']])->name('system.point_control_add');
        //点控设置删除
        $api->delete('pointControlDelete', ['uses' => 'PointControlController@pointControlDelete', 'permission' => ['admin', '点控设置删除', '点控设置', 'v1']])->name('system.point_control_delete');
        //点控设置-历史记录
        $api->get('historicRecords', ['uses' => 'PointControlController@historicRecords', 'permission' => ['admin', '点控设置历史记录', '点控设置', 'v1']])->name('system.historic_records');
        //禁止礼金设置
        $api->match(['get', 'post'], 'forbidGifts', ['uses' => 'SystemController@forbidGifts', 'permission' => ['admin', '禁止礼金设置', '系统全局设置', 'v1']])->name('system.forbid_gifts');

        //获取四方代付设置
        $api->get('remitList', ['uses' => 'RemitConfigController@getList', 'permission' => ['admin', '获取四方代付设置', '系统管理', 'v1']])->name('system.remit_list');
        //编辑四方代付设置
        $api->match(['get', 'post'],'remitEdit/{id}', ['uses' => 'RemitConfigController@edit', 'permission' => ['admin', '编辑四方代付设置', '系统管理', 'v1']])->name('system.remit_edit');

    });
});

<?php
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    //登陆路由
    $api->group(['prefix' => 'client', 'namespace' => '\Modules\Client\Http\Controllers'], function ($api) {
        //短信验证码
        $api->any('/message/code', 'ClientController@code');
        //获取包信息
        $api->get('/version/{appid}', 'ClientController@index');
        //下单
        $api->post('/order/withdrawal', 'OrderController@withdrawal');
        //订单列表
        $api->post('/order/withdrawal/list', 'OrderController@withdrawalList');
        //表单信息
        $api->post('/order/withdrawal/address', 'OrderController@withdrawAddress');
        //充值订单列表
        $api->post('/order/pay/list', 'OrderController@payList');
        //游戏全局设置信息
        $api->get('/system/setting', 'SystemController@setting');
        //技术支持
        $api->get('system/support', 'SystemController@support');
        //新闻公告
        $api->get('system/notice', 'SystemController@notice');
        //轮播广告和网址
        $api->get('system/carousel', 'SystemController@carousel');
        //用户分享接口
        $api->get('system/user_share', 'SystemController@user_share');

        //赚金说明
        $api->get('agent/profitExplain/{user_id}', 'AgentController@profitExplain');
        //赚金说明新
        $api->get('agent/newProfitExplain', 'AgentController@newProfitExplain');
        //我的推广
        $api->get('agent/profitShare', 'AgentController@profitShare');
        //个人业绩-直推总览
        $api->get('agent/personalOverview', 'AgentController@personalOverview');
        //个人业绩-直推报表
        $api->get('agent/personalReportForms', 'AgentController@personalReportForms');
        //我的玩家
        //$api->get('agent/memberDetails', 'AgentController@memberDetails');
        //我的业绩
        //$api->get('agent/myBrokerage', 'AgentController@myBrokerage');
        //我的奖励
        //$api->get('agent/promoteEarnings', 'AgentController@promoteEarnings');
        //我的
        $api->get('agent/withdrawDetails', 'AgentController@withdrawDetails');

        $api->post('agent/withdraw', 'AgentController@withdraw');
        //绑定代理
        $api->post('agent/binding', 'AgentController@binding');
        //获取游戏记录
        $api->get('game/getRecordDrawScoreForWeb', 'GameController@getRecordDrawScoreForWeb');
        //获取游戏下载二维码
        $api->get('game/getQrcode', 'GameController@getQrcode');
        //刷新金币
        $api->get('user/refreshGold/{user_id}', 'UserController@refreshGold');
        //金币流水
        $api->get('user/goldWater/{user_id}', 'UserController@goldWater');
        //转账记录
        $api->get('user/transferLog/{user_id}', 'UserController@transferLog');
        //收款记录
        $api->get('user/receiverLog/{user_id}', 'UserController@receiverLog');
        //用户头像
        $api->get('face/{user_id}', 'UserController@face');
        //vip商人
        $api->get('system/vip_trader', 'SystemController@vip_trader');
        //转盘配置接口
        $api->get('system/turntable_config', 'SystemController@turntable_config');
        //红包配置接口
        $api->get('system/red_packet_config', 'SystemController@red_packet_config');
        //游戏推广
        $api->post('accountsBinding','GameController@accountsBinding');
        //用户发送邮件
        $api->post('user/sendEmail','EmailController@sendEmail');
        //用户发送邮件列表
        $api->get('user/sendEmailList','EmailController@sendEmailList');
        //用户接收邮件列表
        $api->get('user/receiveEmailList','EmailController@receiveEmailList');
        //删除发件箱的邮件
        $api->get('user/sendEmailDel','EmailController@sendEmailDel');
        //团队业绩-团队总览
        $api->get('agent/teamOverview/{user_id}','AgentController@teamOverview');
        //团队业绩-团队报表
        $api->get('agent/teamReportForms/{user_id}','AgentController@teamReportForms');
        //团队业绩-业绩来源
        $api->get('agent/teamReportDetail/{user_id}','AgentController@teamReportDetail');
        //推广红包-我的推广任务
        $api->get('agent/payList/{user_id}','AgentController@payList');
        //用户检测接口
        $api->get('system/user_check', 'SystemController@user_check');
        //代理红包列表
        $api->get('agent/red/packet','AgentRedPacketController@list');
        //领取红包
        $api->post('agent/get/packet','AgentRedPacketController@getRedPacket');
        //刷新稽核打码量
        $api->get('user/auditBet/{user_id}', 'UserController@auditBetInfo');
        //资金明细-稽核打码量
        $api->get('user/auditBetList/{user_id}', 'UserController@auditBetList');
        //返利活动图片
        $api->get('system/rebateActivity', 'SystemController@rebate_activity');
        //外接平台登录
        //$api->post('outerPlatformGameLogin/{alias}','GamePlatformController@outerPlatformGameLogin');
        //外接平台下分
        //$api->post('outerPlatformGameDeduction','GamePlatformController@outerPlatformGameDeduction');
        //游戏分类
        $api->get('gameCategory','GameController@gameCategory');
        //投注记录
        $api->get('jettonScoreRecord/{user_id}','UserController@jettonScoreRecord');
        //账户明细
        $api->get('accountDetail/{user_id}','UserController@accountDetail');
        //个人报表
        $api->get('accountReport/{user_id}','UserController@accountReport');
        //获取洗码中的游戏分类
        $api->get('washCodeGameCategory','WashCodesController@getWashCodeGameCategory');
        //洗码列表中的有效投注，上次领取时间，总洗码量
        $api->get('washCodeData/{game_id}','WashCodesController@getWashCodeData');
        //洗码列表中的按分类未洗码的记录
        $api->get('washCodeList/{category_id}/{game_id}','WashCodesController@getWashCodeList');
        //获取游戏分类下的平台或者游戏
        $api->get('platformOrGame/{category_id}','WashCodesController@getPlatformOrGame');
        //获取用户洗码比例
        $api->get('washCodeRetio/{category_id}/{platform_id}/{kind_id}/{game_id}','WashCodesController@getWashCodeRetio');
        //洗码记录
        $api->get('getWashCodeHistory/{user_id}','WashCodesController@getWashCodeHistory');
        //洗码明细
        $api->get('getWashCodeRecord/{wosh_code_history_id}','WashCodesController@getWashCodeRecord');
        //个人中心过滤数据
        $api->get('accountFilterData','UserController@accountFilterData');
        //注册信息设置
        $api->get('getRegisterInfo','SystemController@getRegisterInfo');
        //获取分类游戏列表
        $api->get('subGameCategory','GameController@subGameCategory');
        //获取用户层级
        $api->get('userLevel','UserController@userLevel');
        // 活动中心——获取左侧分类
        $api->get('activityCenter/{type}','ClientController@getActivityCenter');
        // 获取洗码
        $api->get('getWashCodeScore','WashCodesController@getWashCodeScore');
        // 获取VipInfo
        $api->get('getVipInfo/{id}','ClientController@getVipInfo');

        //领取首充签到
        $api->post('firstChargeSignInReceive/{user_id}','ActivityController@firstChargeSignInReceive');

        // 答题活动提交
        $api->post('answer_give_submit','SystemController@answer_give_submit');
        //所有活动配置
        $api->get('activityConfig','SystemController@activityConfig');

        //转盘领取
        $api->post('rotary_draw','SystemController@rotary_draw');
        //全服记录
        $api->get('prize_list','SystemController@prize_list');
        //转盘个人记录
        $api->get('rotary_user_list','SystemController@getRotaryRecordList');

        //彩金领取
        $api->post('handsel_give','SystemController@handselGive');
        //回血返利列表
        $api->get('return_rebate_list','SystemController@returnRebateList');
        //近日返利排行榜
        $api->get('return_rebate_rank','SystemController@returnRebateRank');
    });
});


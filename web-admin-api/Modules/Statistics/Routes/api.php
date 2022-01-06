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
    //统计路由组
    $api->group(['prefix' => 'statistics', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Statistics\Http\Controllers'], function ($api) {
        $api->get('gameList', ['uses' => 'StatisticsController@global_statistics', 'permission' => ['admin', '表格展示', '全局统计', 'v1']])->name('statistics.global_statistics'); //全局统计
        $api->get('winLose', ['uses' => 'StatisticsController@real_time_win_lose', 'permission' => ['admin', '折线图展示', '全局统计', 'v1']])->name('statistics.win_lose');          //全局统计-实时输赢
        $api->get('registerList', ['uses' => 'StatisticsController@register_statistics', 'permission' => ['admin', '注册统计', '每日统计', 'v1']])->name('statistics.register');    //每日统计-注册统计
        $api->get('taxationList', ['uses' => 'StatisticsController@taxation_statistics', 'permission' => ['admin', '盈利统计', '每日统计', 'v1']])->name('statistics.taxation');    //每日统计-盈利统计
        //$api->get('rateList', ['uses' => 'StatisticsController@retention_rate', 'permission' => ['admin', '留存率', '留存率', 'v1']])->name('statistics.retention_rate');       //统计管理-留存率
        //$api->get('channelRateList', ['uses' => 'StatisticsController@channel_retention_rate', 'permission' => ['admin', '渠道留存率', '留存率', 'v1']])->name('statistics.channel_retention_rate'); //统计管理-渠道留存率
        $api->get('flow', ['uses' => 'StatisticsController@statics_flow', 'permission' => ['admin', '当日流水', '当日流水', 'v1']])->name('statistics.statics_flow');//每日流水
        $api->get('innerFlow', ['uses' => 'StatisticsController@statics_flow', 'permission' => ['admin', '内部充值订单-当日流水', '当日流水', 'v1']])->name('statistics.inner_flow');//每日流水
        $api->get('vipFlow', ['uses' => 'StatisticsController@statics_flow', 'permission' => ['admin', 'VIP商人充值订单-当日流水', '当日流水', 'v1']])->name('statistics.vip_flow');//每日流水
        $api->get('userWithdrawFlow', ['uses' => 'StatisticsController@statics_flow', 'permission' => ['admin', '用户提现订单-当日流水', '当日流水', 'v1']])->name('statistics.user_withdraw_flow');//每日流水
        $api->get('userFinanceFlow', ['uses' => 'StatisticsController@statics_flow', 'permission' => ['admin', '财务用户订单-当日流水', '当日流水', 'v1']])->name('statistics.user_finance_flow');//每日流水
        // $api->get('/remedyRetention', ['uses' => 'RemedyController@retention', 'permission' => ['admin', '留存率补救', '统计管理', 'v1']])->name('statistics.remedy_retention');       //留存率补救
        $api->get('winCount', ['uses' => 'StatisticsController@winCount', 'permission' => ['admin', '盈利二级统计', '每日统计', 'v1']])->name('statistics.win_count');    //每日统计-盈利统计-二级统计
    });
    //首页路由组 ['auth:admin', 'admin']
    $api->group(['prefix' => 'homepage', 'middleware' => ['auth:admin'], 'namespace' => '\Modules\Statistics\Http\Controllers'], function ($api) {
        $api->get('chartWinLose', ['uses' => 'HomePageController@chart_winlose'])->name('homepage.chart_winlose');//首页-输赢状况金额
        $api->get('chartPay', ['uses' => 'HomePageController@chart_pay'])->name('homepage.chart_pay');//首页-充值量
        $api->get('chartBet', ['uses' => 'HomePageController@chart_bet'])->name('homepage.chart_bet');//首页-有效投注
        $api->get('chartOnline', ['uses' => 'HomePageController@chart_online'])->name('homepage.chart_online');//首页-在线玩家
        $api->get('reportForms', ['uses' => 'HomePageController@report_forms'])->name('homepage.report_forms');//首页-报表呈现
        $api->get('PolygonalChartOnline', ['uses' => 'HomePageController@polygonal_chart_online'])->name('homepage.polygonal_chart_online');//首页-在线玩家折线图
    });

    //报表路由组
    $api->group(['prefix' => 'report', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Statistics\Http\Controllers'], function ($api) {
        //系统报表
        $api->get('systemReportForm', ['uses' => 'SystemReportFormController@getInfo', 'permission' => ['admin', '系统报表', '报表系统', 'v1']])->name('report.system_report_form');
        //系统报表内外部充值详情
        $api->get('payReportDetails', ['uses' => 'SystemReportFormController@payReportDetails', 'permission' => ['admin', '系统报表内外部充值详情', '报表系统', 'v1']])->name('report.pay_report_details');
        //系统报表-平台盈利详情
        $api->get('platformProfitDetails', ['uses' => 'SystemReportFormController@platformProfitDetails', 'permission' => ['admin', '系统报表平台盈利详情', '报表系统', 'v1']])->name('report.platform_profit_details');
        //系统报表-有效投注详情
        $api->get('jettonScoreDetails', ['uses' => 'SystemReportFormController@jettonScoreDetails', 'permission' => ['admin', '系统报表有效投注详情', '报表系统', 'v1']])->name('report.jetton_score_details');
        //系统报表-中奖金额详情
        $api->get('prizeMoneysDetails', ['uses' => 'SystemReportFormController@prizeMoneysDetails', 'permission' => ['admin', '系统报表中奖金额详情', '报表系统', 'v1']])->name('report.prize_moneys_details');
        //系统报表-流水详情
        $api->get('streamScoreDetails', ['uses' => 'SystemReportFormController@streamScoreDetails', 'permission' => ['admin', '系统报表流水详情', '报表系统', 'v1']])->name('report.stream_score_details');
        //玩家余额统计
        $api->get('accountBalanceList', ['uses' => 'SystemReportFormController@accountBalanceList']);

        //返利报表
        $api->get('rebateReportForm', ['uses' => 'RebateReportFormController@getInfo', 'permission' => ['admin', '返利报表', '报表系统', 'v1']])->name('report.rebate_report_form');
        //返利报表中推广的用户详情
        $api->get('rebateReportDetails', ['uses' => 'RebateReportFormController@rebateReportDetails', 'permission' => ['admin', '返利报表推广用户详情', '报表系统', 'v1']])->name('report.rebate_report_details');
        //渠道报表
        $api->get('channelReportForm', ['uses' => 'ChannelReportFormController@getInfo', 'permission' => ['admin', '渠道报表', '报表系统', 'v1']])->name('report.channel_report_form');
        //活动报表
        $api->get('activityReport', ['uses' => 'ActivityController@activityReport', 'permission' => ['admin', '活动报表', '报表系统', 'v1']])->name('report.activity_report');
        //活动报表详情
        $api->get('activityReportDetails', ['uses' => 'ActivityController@activityReportDetails', 'permission' => ['admin', '活动报表详情', '报表系统', 'v1']])->name('report.activity_report_details');
        //代理报表
        $api->get('agentReport', ['uses' => 'AgentController@agentReport', 'permission' => ['admin', '代理报表', '报表系统', 'v1']])->name('report.agent_report');
        //代理报表 - 总计
        $api->get('agentTotal', ['uses' => 'AgentController@agentTotal', 'permission' => ['admin', '代理报表-总计', '报表系统', 'v1']])->name('report.agent_report_total');
        //代理佣金报表
        $api->get('agentBalanceReport', ['uses' => 'AgentController@agentBalanceReport', 'permission' => ['admin', '代理佣金报表', '报表系统', 'v1']])->name('report.agent_balance_report');
        //代理佣金报表详情
        $api->get('agentBalanceReportDetails', ['uses' => 'AgentController@agentBalanceReportDetails', 'permission' => ['admin', '代理佣金报表详情', '报表系统', 'v1']])->name('report.agent_balance_report_details');
        //代理佣金报表详情-业绩来源
        $api->get('teamReportDetail', ['uses' => 'AgentController@teamReportDetail']);
        //用户报表
        $api->get('userReport', ['uses' => 'UserController@userReport', 'permission' => ['admin', '用户报表', '报表系统', 'v1']])->name('report.user_report');
        //游戏报表
        $api->get('gameReportForm', ['uses' => 'GameReportFormController@getInfo', 'permission' => ['admin', '游戏报表', '报表系统', 'v1']])->name('report.game_report_form');

        //实时推广数据
        $api->get('realChannelReport', ['uses' => 'GameReportFormController@getRealList', 'permission' => ['admin', '实时推广数据', '报表系统', 'v1']])->name('report.real_channel');
        //推广用户付费
        $api->get('channelPayReport', ['uses' => 'GameReportFormController@getChannelPay', 'permission' => ['admin', '推广用户付费', '报表系统', 'v1']])->name('report.channel_pay');
        //日活跃状态
        $api->get('daysActiveReport', ['uses' => 'GameReportFormController@getDaysActive', 'permission' => ['admin', '日活跃状态', '报表系统', 'v1']])->name('report.days_active');

        //支付概况报表
        $api->get('paymentOverviewReport', ['uses' => 'PaymentOverviewController@paymentList', 'permission' => ['admin', '支付概况报表', '报表系统', 'v1']])
            ->name('report.payment_overview_report');
        //注册用户留存报表
        $api->get('registerRetention', ['uses' => 'RetentionReportFormController@register_retention', 'permission' => ['admin', '注册用户留存报表', '报表系统', 'v1']])
            ->name('report.register_retention');
        //首充用户留存报表
        $api->get('firstRechargeRetention', ['uses' => 'RetentionReportFormController@first_recharge_retention', 'permission' => ['admin', '首充用户留存报表', '报表系统', 'v1']])
            ->name('report.first_recharge_retention');
        //线上支付总汇
        $api->get('onlinePayment', ['uses' => 'PayReportController@onlinePayment', 'permission' => ['admin', '线上支付总汇', '报表系统', 'v1']])->name('report.online_payment');
        //今日注册信息
        $api->get('todayRegister', ['uses' => 'PayReportController@todayRegister', 'permission' => ['admin', '今日注册信息', '报表系统', 'v1']])->name('report.today_register');
        //今日首充信息
        $api->get('todayFirstPayment', ['uses' => 'PayReportController@todayFirstPayment', 'permission' => ['admin', '今日首充信息', '报表系统', 'v1']])->name('report.today_first_payment');
        //支付排行榜
        $api->get('paymentRank', ['uses' => 'PayReportController@paymentRank', 'permission' => ['admin', '支付排行榜', '报表系统', 'v1']])->name('report.payment_rank');
        //日充值排行榜
        $api->get('todayPayRank', ['uses' => 'PayReportController@todayPayRank', 'permission' => ['admin', '日充值排行榜', '报表系统', 'v1']])->name('report.today_pay_rank');
        //支付用户留存
        $api->get('payUserKeep', ['uses' => 'PayReportController@payUserKeep', 'permission' => ['admin', '支付用户留存', '报表系统', 'v1']])->name('report.pay_user_keep');

        //vip人数统计
        $api->get('vipStatistics', ['uses' => 'UserController@vipStatistics', 'permission' => ['admin', 'vip人数统计', '报表系统', 'v1']])->name('report.vip_statistics');
    });
    //新的首页路由组
    $api->group(['prefix' => 'newhomepage', 'middleware' => ['auth:admin'], 'namespace' => '\Modules\Statistics\Http\Controllers'], function ($api) {
        //首页-净盈利和税费金额
        $api->get('getProfit', ['uses' => 'NewHomePageController@getProfit'])->name('newhomepage.get_profit');
        //首页-充值金额
        $api->get('getRecharge', ['uses' => 'NewHomePageController@getRecharge'])->name('newhomepage.get_recharge');

        $api->get('getWithdrawal', ['uses' => 'NewHomePageController@getWithdrawal'])->name('newhomepage.get_withdrawal');
        //首页-活动礼金
        $api->get('getActivityGift', ['uses' => 'NewHomePageController@getActivityGift'])->name('newhomepage.get_activity_gift');
        //首页-游戏数据
        $api->get('getGameData', ['uses' => 'NewHomePageController@getGameData'])->name('newhomepage.get_game_data');
        //首页-活动礼金领取详细数据
        $api->get('getCashGiftDetails', ['uses' => 'NewHomePageController@getCashGiftDetails'])->name('newhomepage.cash_gift_details');
        //首页-3种设备，各设备登录游戏的人数统计
        $api->get('getLogonPeopleTotal', ['uses' => 'NewHomePageController@getLogonPeopleTotal'])->name('newhomepage.logon_people_total');
    });

});

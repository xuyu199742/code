<?php
/*留存报表*/

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\FirstRechargeLogs;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\StatisticsRetentionChannels;
use Models\AdminPlatform\StatisticsRetentions;
use Models\Agent\ChannelInfo;
use Models\Agent\ChannelUserRelation;
use Transformers\PayUserKeepReportTransformer;
use Validator;

class RetentionReportFormController extends Controller
{
	/*注册用户留存*/
	public function register_retention(Request $request)
	{
		Validator::make($request->all(), [
			'channel_id' => ['nullable', 'numeric'],
			'year'       => ['nullable', 'numeric'], //年
			'month'      => ['nullable', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],//月
		], [
			'channel_id.numeric' => '渠道ID必须数字',
			'year.required'      => '年份必填',
			'year.numeric'       => '年份必须是数字',
			'month.required'     => '月份必选',
			'month.in'           => '月份不在可选范围内'
		])->validate();
		$year = $request->input('year') ?? date('Y');
		$month = $request->input('month') ?? date('m');
		if ($year == date('Y') && $month == date('m')) {
			$start_date = date('Y-m-01', strtotime(date('Y-m-d')));
		} else {
			$start_date = $year . '-' . $month . '-01';
		}
		$end_date = date('Y-m-d', strtotime("$start_date +1 month -1 day"));
		$dates = getDateRange($start_date, $end_date, 35);
		$channel_id = $request->input('channel_id'); //渠道id
		if ($channel_id) {
			$is_exit = ChannelInfo::where('channel_id', $channel_id)->first();
			if (!$is_exit) {
				return ResponeFails('该渠道不存在，请重新输入');
			}
			//渠道总注册人数（按注册日期汇总）
			$register_sum_total = ChannelUserRelation::from(ChannelUserRelation::tableName() . ' AS a')
				->leftJoin(AccountsInfo::tableName() . ' AS b', 'a.user_id', '=', 'b.UserID')
				->select(
					\DB::raw('CONVERT(varchar(100), b.RegisterDate, 23) as register_time'),
					\DB::raw('COUNT(*) as total')
				)
				->where('a.channel_id', $channel_id)
				->andFilterBetweenWhere('b.RegisterDate', $start_date, $end_date)
				->groupBy(\DB::raw('CONVERT(varchar(100), b.RegisterDate, 23)'))
				->pluck('total', 'register_time');
			//渠道注册用户留存
			$result = StatisticsRetentionChannels::andFilterBetweenWhere('statistics_time', $start_date, $end_date)
				->select('statistics_time', 'type', \DB::raw('SUM(total) AS total'))
				->where('channel_id', $channel_id)
				->groupBy('statistics_time', 'type')
				->orderBy('type')->get();
			$result = collect($result->toArray())->groupBy('statistics_time');
		} else {
			//系统总注册人数（按注册日期汇总）
			$register_sum_total = AccountsInfo::select(
				\DB::raw("CONVERT(varchar(100), RegisterDate, 23) as register_time"),
				\DB::raw('COUNT(UserID) as sum_total')
			)
				->where('IsAndroid', 0)
				->andFilterBetweenWhere('RegisterDate', $start_date, $end_date)
				->groupBy(\DB::raw("CONVERT(varchar(100), RegisterDate, 23)"))
				->pluck('sum_total', 'register_time');
			//系统注册用户留存
			$result = StatisticsRetentions::select('statistics_time', 'type', 'total')->andFilterBetweenWhere('statistics_time', $start_date, $end_date)
				->orderBy('type')->get();
			$result = collect($result->toArray())->groupBy('statistics_time');
		}
		$list = [];
		foreach ($dates as $k => $v) {
			$type = collect($result[$v] ?? [])->keyBy('type');
			$list[$k]['date'] = $v;
			$list[$k]['register_total'] = $register_sum_total[$v] ?? 0;
			if ($list[$k]['register_total'] > 0) {
				$list[$k]['next_day'] = (($type[1]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['third_day'] = (($type[2]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['fourth_day'] = (($type[3]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['fifth_day'] = (($type[4]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['sixth_day'] = (($type[5]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['seventh_day'] = (($type[6]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['fifteenth_day'] = (($type[14]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['thirtieth_day'] = (($type[29]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
				$list[$k]['sixtieth_day'] = (($type[59]['total'] ?? 0) / $list[$k]['register_total']) * 100 ?? 0;
			} else {
				$list[$k]['next_day'] = 0;
				$list[$k]['third_day'] = 0;
				$list[$k]['fourth_day'] = 0;
				$list[$k]['fifth_day'] = 0;
				$list[$k]['sixth_day'] = 0;
				$list[$k]['seventh_day'] = 0;
				$list[$k]['fifteenth_day'] = 0;
				$list[$k]['thirtieth_day'] = 0;
				$list[$k]['sixtieth_day'] = 0;
			}
		}
		return ResponeSuccess('请求成功', $list);
	}

	/*首充用户留存*/
	public function first_recharge_retention(Request $request)
	{
		//查询某一天的用户2,3,7,15,30,60日的留存
		Validator::make($request->all(), [
			'channel_id' => ['nullable', 'numeric'],

		], [
			'channel_id.numeric' => '渠道ID必须数字',
		])->validate();
		if (request('channel_id')) {
			$is_exit = ChannelInfo::where('channel_id', request('channel_id'))->first();
			if (!$is_exit) {
				return ResponeFails('该渠道不存在，请重新输入');
			}
		}
		$sub = PaymentOrder::from(PaymentOrder::tableName() . ' as a')
			->select(
				\DB::raw("MIN (a.id) AS id"),
				\DB::raw("b.channel_id as channel_id")
			)
			->leftJoin(ChannelUserRelation::tableName() . ' as b', 'a.user_id', '=', 'b.user_id')
			->andFilterWhere('b.channel_id', request('channel_id'))
			->where('a.payment_status', PaymentOrder::SUCCESS)
			->groupBy(\DB::raw("a.user_id,b.channel_id"));
		$list = PaymentOrder::from(PaymentOrder::tableName() . ' as d')
			->select(
				\DB::raw("format (d.created_at, 'yyyy-MM-dd') AS ctime"),
				\DB::raw("COUNT (DISTINCT d.user_id) AS num"),
				\DB::raw("SUM (d.amount) AS amount")
			)
			->rightJoinSub($sub, 'c', 'c.id', '=', 'd.id')
			->groupBy(\DB::raw("format (d.created_at, 'yyyy-MM-dd')"))
            ->orderBy(\DB::raw("format (d.created_at, 'yyyy-MM-dd')"),'desc')
			->paginate(10);

		//查询首充用户登录情况
		foreach ($list as $k => $v) {
			//获取30天的日期
			$etime_two = date('Y-m-d', strtotime($v->ctime) + 1 * 86400);
			$etime_three = date('Y-m-d', strtotime($v->ctime) + 2 * 86400);
			$etime_seven = date('Y-m-d', strtotime($v->ctime) + 6 * 86400);
			$etime_fifteen = date('Y-m-d', strtotime($v->ctime) + 14 * 86400);
			$etime_thirty = date('Y-m-d', strtotime($v->ctime) + 29 * 86400);
			$etime_sixty = date('Y-m-d', strtotime($v->ctime) + 59 * 86400);

			//查询该日期的2,3,7,15,30,60日的留存
			$list[$k]['two'] = 0;
			$list[$k]['three'] = 0;
			$list[$k]['seven'] = 0;
			$list[$k]['fifteen'] = 0;
			$list[$k]['thirty'] = 0;
			$list[$k]['sixty'] = 0;
			$list[$k]['two'] = (($this->getFirstRechargeRetention($v->ctime, $etime_two, 2) ?? 0) / $list[$k]['num']) * 100;
			if ($list[$k]['two'] == 0) {
				continue;
			}
			$list[$k]['three'] = (($this->getFirstRechargeRetention($v->ctime, $etime_three, 3) ?? 0) / $list[$k]['num']) * 100;
			if ($list[$k]['three'] == 0) {
				continue;
			}
			$list[$k]['seven'] = (($this->getFirstRechargeRetention($v->ctime, $etime_seven, 7) ?? 0) / $list[$k]['num']) * 100;
			if ($list[$k]['seven'] == 0) {
				continue;
			}
			$list[$k]['fifteen'] = (($this->getFirstRechargeRetention($v->ctime, $etime_fifteen, 15) ?? 0) / $list[$k]['num']) * 100;
			if ($list[$k]['fifteen'] == 0) {
				continue;
			}
			$list[$k]['thirty'] = (($this->getFirstRechargeRetention($v->ctime, $etime_thirty, 30) ?? 0) / $list[$k]['num']) * 100;
			if ($list[$k]['thirty'] == 0) {
				break;
			}
		}
		return $this->response->paginator($list, new PayUserKeepReportTransformer());
	}

	/** 首充用户留存获取*/
	private function getFirstRechargeRetention($start_date, $end_date, $num)
	{
		$sub = PaymentOrder::from(PaymentOrder::tableName() . ' as a')
			->select(
				\DB::raw("a.user_id"),
				\DB::raw("format(min(a.created_at),'yyyy-MM-dd') as ctime")
			)
			->leftJoin(ChannelUserRelation::tableName() . ' as b', 'a.user_id', '=', 'b.user_id')
			->andFilterWhere('b.channel_id', request('channel_id'))
			->where('a.payment_status', PaymentOrder::SUCCESS)
			->groupBy(\DB::raw("a.user_id"));
		$sub1 = PaymentOrder::from(PaymentOrder::tableName() . ' as d')
			->select(
				\DB::raw("d.user_id"),
				\DB::raw("COUNT(DISTINCT format(d.created_at,'yyyy-MM-dd')) as total")
			)
			->rightJoinSub($sub, 'c', 'c.user_id', '=', 'd.user_id')
			->where('c.ctime',$start_date)
            ->where('d.payment_status', PaymentOrder::SUCCESS)
			->where('d.created_at', '>=', $start_date)
			->where('d.created_at', '<=', $end_date . ' 23:59:59')
			->groupBy(\DB::raw("d.user_id"))
			->having(\DB::raw("COUNT(DISTINCT format(d.created_at,'yyyy-MM-dd'))"), '=', $num);
		$total = \DB::table(\DB::raw("({$sub1->toSql()}) as f"))->mergeBindings($sub1->getQuery())->count();
		return $total;
	}
}
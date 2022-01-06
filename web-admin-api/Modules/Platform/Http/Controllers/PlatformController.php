<?php

namespace Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Models\Accounts\AccountsInfo;
use Models\OuterPlatform\Company;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\GameMissingData;
use Models\OuterPlatform\GameMissingOrder;
use Models\OuterPlatform\GameRoom;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformCategory;
use Models\OuterPlatform\OuterPlatformGame;
use Models\OuterPlatform\OuterPlatformInout;
use Models\Platform\GameRoomInfo;
use Models\Treasure\RecordGameScore;
use Modules\Platform\Http\Requests\GetCategoryForGameRequest;
use Modules\Platform\Http\Requests\OuterPlatformGameRequest;
use Modules\Platform\Http\Requests\OuterPlatformRequest;
use Modules\Platform\Packages\lib\JDBGamePlatform;
use Modules\Platform\Packages\signature\KYGamePlatform;
use Transformers\OuterPlatformGameRecordTransformer;
use Transformers\OuterPlatformGameTransformer;
use Transformers\OuterPlatformInoutTransformer;
use Transformers\OuterPlatformTransformer;

class PlatformController extends Controller
{
    /**
     * 用于筛选框接口获取平台或游戏
     *
     */
    public function getPlatformOrGame()
    {
        $list = OuterPlatform::with(['games' => function ($query) {
            $query->select('id', 'platform_id', 'name', 'kind_id');
            //->where('status',OuterPlatformGame::STATUS_ON)
            //->where('server_status',OuterPlatformGame::SERVER_STATUS_ON);
        }])
            //->where('status',OuterPlatform::STATUS_ON)
            ->select('id', 'name', 'alias')
            ->get();
        return ResponeSuccess('获取成功', $list);
    }

    /**
     * 用于选择平台
     *
     */
    public function getPlatform()
    {
        $type = intval(request('category_id'));
        if ($type <= 1) {
            $list = OuterPlatform::select('id', 'name', 'alias')->get();

        } else {
            $num = pow(2, $type - 2);
            $list = OuterPlatform::select('id', 'name', 'alias')->where(\DB::raw("(owned&" . $num . ")"), $num)->get();
        }
        return ResponeSuccess('获取成功', $list);
    }

    /**
     * 用于选择平台游戏
     *
     */
    public function getPlatformGame()
    {
        $list = OuterPlatformGame::select('id', 'name')->where('platform_id', request('platform_id'))->get();
        return ResponeSuccess('获取成功', $list);
    }

    /**
     * 选择分类对应的游戏
     *
     */
    public function getCategoryForGame(GetCategoryForGameRequest $request)
    {
        $games = GameCategoryRelation::query()->with('platform:id,name')
            ->where('category_id', $request->category_id)
            ->get();

        return ResponeSuccess('获取成功', $games);
    }

    /**
     * 获取平台列表
     *
     */
    public function platformList()
    {
        $list = OuterPlatform::orderBy('id', 'asc')->get();
        return $this->response->collection($list, new OuterPlatformTransformer());
    }


    /**
     * 编辑外接平台
     *
     */
    public function platformEdit(OuterPlatformRequest $request, $alias)
    {
        if (request()->isMethod('get')) {
            $data = OuterPlatform::where('alias', $alias)->first();
            if (empty($data)) {
                return ResponeFails('查询失败');
            }
            /*$category = GameCategory::where('id','>',1)->select('name','id')->orderBy('sort')->get();
            $category_arr = $category->pluck('id')->toArray();
            asort($category_arr);
            $i = 0;
            foreach ($category_arr as $k => $v){
                $category[$k]['owned'] = $data->owned & pow(2,$i) ? 1 : 0;
                $i++;
            }
            $data->category = $category;*/
            return $this->response->item($data, new OuterPlatformTransformer());
        } elseif (request()->isMethod('post')) {
            //判断平台是否配置中的，不允许新增其他平台

            if (!OuterPlatform::saveOne($alias)) {
                return ResponeFails('编辑失败');
            }
            return ResponeSuccess('编辑成功');
        }
    }

    /**
     * 获取平台游戏列表
     *
     */
    public function platformGameList()
    {
        \Validator::make(request()->all(), [
            'game_name' => ['nullable'],
            'platform_id' => ['required'],
            'status' => ['nullable', 'in:0,1,2'],
        ])->validate();
        $list = OuterPlatformGame::where('platform_id', request('platform_id'))->andFilterWhere('name', 'LIKE', '%' . request('game_name') . '%')->andFilterWhere('status', request('status'))->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->get();
        return $this->response->collection($list, new OuterPlatformGameTransformer());
    }


    /**
     * 编辑外接平台游戏
     *
     */
    public function platformGameEdit(OuterPlatformGameRequest $request)
    {
        if (request()->isMethod('get')) {
            $data = OuterPlatformGame::where('platform_alias', request('platform_alias'))->where('kind_id', request('kind_id'))->first();
            return $this->response->item($data, new OuterPlatformGameTransformer());
        } elseif (request()->isMethod('post')) {
            $OuterPlatform = OuterPlatform::where('alias', request('platform_alias'))->first();
            if (empty($OuterPlatform)) {
                return ResponeFails('平台别名有误');
            }
            //判断游戏是否在配置中的，不允许新增其他游戏
            if (!OuterPlatformGame::saveOne($OuterPlatform->id)) {
                return ResponeFails('编辑失败');
            }
            return ResponeSuccess('编辑成功');
        }
    }

    /**
     * 三方游戏平台注单查询（获取游戏记录）
     * @param time_type 1 update_time 2 order_time
     * @return json
     */
    public function gameDripSheet()
    {
        \Validator::make(request()->all(), [
            'check_game' => 'nullable|array',
            'check_game.*' => 'integer',
            'game_id' => 'nullable|integer',
            'time_type' => 'nullable|in:1,2',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ], [
            'check_game.array' => '平台ID数据为数组',
            'check_game.*.integer' => '平台ID必须数字',
            'game_id.integer' => '游戏ID必须数字',
            'time_type.in' => '传参有误',
            'start_date.date' => '无效日期',
            'end_date.date' => '无效日期',
        ])->validate();
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $platform_ids = request('check_game', []);
        $page = request('page') ?? 1;
        $type = request('time_type') ?? 1;
        if($type == 2) {
            $time_type = ['a.OrderTime', 'OrderTime'];
        } else {
            $time_type = ['a.UpdateTime', 'UpdateTime'];
        }
        $basic_query = RecordGameScore::from(RecordGameScore::tableName() . ' as a')
            ->leftJoin(AccountsInfo::tableName() . ' as b', 'a.UserID', '=', 'b.UserID')
            ->leftJoin(OuterPlatform::tableName() . ' as c', 'a.PlatformID', '=', 'c.id')
            ->leftJoin(OuterPlatformGame::tableName() . ' as d', function ($join) {
                $join->on('a.KindID', '=', 'd.kind_id')->on('a.PlatformID', '=', 'd.platform_id');
            })
            ->leftJoin(GameRoomInfo::tableName() . ' as e', function ($join) {
                $join->on('a.ServerID', '=', 'e.ServerID')->where('a.PlatformID', '<', 2000);//查询官方平台的
            })
            ->leftJoin(GameRoom::tableName() . ' as f', function ($join) {
                $join->on('d.id', '=', 'f.platform_gameid')->on('a.ServerID', '=', 'f.room_id');//查询官方平台的
            })
            ->when($platform_ids, function ($query) use ($platform_ids) {
                $query->whereIn('a.PlatformID', $platform_ids);
            })
            ->andFilterWhere('a.OrderNo', request('order_no'))
            ->andFilterBetweenWhere($time_type[0], request('start_date'), request('end_date'))
            ->andFilterWhere('b.GameID', request('game_id'));

        if ($page > 1) {
            $minIdSql = (clone $basic_query)->select('a.ID')->limit($page_list_rows * ($page - 1))->orderBy('a.ID', 'desc');
            $rawSql = str_replace("?", "'%s'", $minIdSql->toSql());
            $rawSql = vsprintf($rawSql, $minIdSql->getBindings());
            $list = (clone $basic_query)->select('a.ID', 'a.UpdateTime', 'a.ChangeScore', 'a.SystemServiceScore', 'a.JettonScore', 'a.OrderNo', 'e.ServerName', 'f.room_name',
                'a.OrderTime', 'a.PlatformID', 'b.GameID', 'c.name as PlatformName', 'c.alias', 'c.have_order_details', 'd.name as KindName')
                ->whereRaw('a.ID < (SELECT MIN(ID) FROM (' . $rawSql . ') t )')
                ->limit($page_list_rows)
                ->orderBy($time_type[0], 'desc')
                ->get()
                ->sortByDesc($time_type[1]);
        } else {
            $list = (clone $basic_query)->select('a.ID', 'a.UpdateTime', 'a.ChangeScore', 'a.SystemServiceScore', 'a.JettonScore', 'a.OrderNo', 'e.ServerName', 'f.room_name',
                'a.OrderTime', 'a.PlatformID', 'b.GameID', 'c.name as PlatformName', 'c.alias', 'c.have_order_details', 'd.name as KindName')
                ->limit($page_list_rows)
                ->orderBy($time_type[0], 'desc')
                ->get()
                ->sortByDesc($time_type[1]);
        }

        $total = (clone $basic_query)->count();

        $list = new LengthAwarePaginator($list, $total, $page_list_rows, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

        //统计
        $sum_profit = RecordGameScore::from('RecordGameScore as a')
            ->leftJoin(AccountsInfo::tableName() . ' as b', 'a.UserID', '=', 'b.UserID')
            ->when($platform_ids, function ($query) use ($platform_ids) {
                $query->whereIn('a.PlatformID', $platform_ids);
            })
            ->andFilterWhere('a.OrderNo', request('order_no'))
            ->andFilterBetweenWhere($time_type[0], request('start_date'), request('end_date'))
            ->andFilterWhere('b.GameID', request('game_id'))
            ->select(
                \DB::raw('SUM(a.SystemServiceScore) AS sum_service'),
                \DB::raw('SUM(a.JettonScore) AS sum_bet'),
                \DB::raw('SUM(a.ChangeScore) AS sum_profit'),
                \DB::raw('SUM(a.StreamScore) AS sum_abs')
            )
            ->first();
        return $this->response->paginator($list, new OuterPlatformGameRecordTransformer())
            ->addMeta('sum_bet', realCoins($sum_profit['sum_bet'] ?? 0))
            ->addMeta('sum_profit', realCoins($sum_profit['sum_profit'] ?? 0))
            ->addMeta('sum_abs', realCoins($sum_profit['sum_abs'] ?? 0))
            ->addMeta('sum_service', realCoins($sum_profit['sum_service'] ?? 0));
    }

    /**
     * 三方平台进出记录
     *
     */
    public function platformInOutRecord()
    {
        \Validator::make(request()->all(), [
            'platform_id' => 'nullable|integer',
            'game_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ], [
            'platform_id.integer' => '平台ID必须数字',
            'game_id.integer' => '游戏ID必须数字',
            'start_date.date' => '无效日期',
            'end_date.date' => '无效日期',
        ])->validate();
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list = OuterPlatformInout::from('outer_platform_inout as a')
            ->select('a.*', 'b.name as platform_name', 'c.name as kind_name')
            ->leftJoin(OuterPlatform::tableName() . ' as b', 'a.platform_id', '=', 'b.id')
            ->leftJoin(OuterPlatformGame::tableName() . ' as c', function ($join) {
                $join->on('a.kind_id', '=', 'c.kind_id')->on('a.platform_id', '=', 'c.platform_id');
            })
            ->andFilterWhere('a.platform_id', request('platform_id'))
            ->andFilterWhere('a.game_id', request('game_id'))
            ->andFilterBetweenWhere('a.created_at', request('start_date'), request('end_date'))
            ->orderBy('a.id','desc');
        $total = clone($list);
        $lists = $list->paginate($page_list_rows);
        $total_user = $total->count(\DB::raw("distinct(a.user_id)"));
        $total_change_score = realCoins($total->where('a.status',1)->sum(\DB::raw('a.quit_score - a.carry_score')));
        return $this->response->paginator($lists, new OuterPlatformInoutTransformer())
            ->addMeta('total_user', $total_user)
            ->addMeta('total_change_score', $total_change_score);
    }

    /**
     * 棋牌游戏获取外接平台列表（区分U3d或H5）
     *
     */
    public function getPlatformList()
    {
        \Validator::make(request()->all(), [
            'status' => 'nullable|in:1,2',
            'type' => 'required|in:1,2',
        ], [
            'status.in' => '状态不在范围内',
            'type.required' => 'H5或U3D类型必传',
            'type.in' => '类型不在范围内',
        ])->validate();
        //获取数据库中的平台信息
        $list = OuterPlatformCategory::from('outer_platform_category as a')
            ->select('a.*', 'b.name', 'b.icon', 'b.icons')
            ->leftJoin(OuterPlatform::tableName() . ' as b', 'a.platform_id', '=', 'b.id')
            ->where('a.type', request('type'))
            ->andFilterWhere('a.status', request('status'))
            ->andFilterWhere('b.name', request('name'))
            ->orderBy('a.sort', 'asc')
            ->get()
            ->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['icon_url'] = '';
            $list[$k]['icons_url'] = '';
            //默认选中的图片
            if (!empty($v['icon'])) {
                $list[$k]['icon_url'] = asset('storage/' . $v['icon']);
            }
            //多张图片
            if (!empty($v['icons'])) {
                $list[$k]['icons_url'] = asset('storage/' . $v['icons']);
            }
        }
        return ResponeSuccess('获取成功', $list);
    }

    /**
     * 遗漏注单列表
     *
     */
    public function gameMissDripSheet()
    {
        \Validator::make(request()->all(), [
            'miss_start_date' => 'nullable|date',
            'miss_end_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'platform_id' => 'nullable|integer',
        ], [
            'miss_start_date.date' => '无效日期',
            'miss_end_date.date' => '无效日期',
            'start_date.date' => '无效日期',
            'end_date.date' => '无效日期',
            'platform_id.integer' => '平台ID必须数字',
        ])->validate();
        // 新增需求，自定义每页条数
        $page_list_rows = request('page_sizes') ?? config('page.list_rows');
        $list = RecordGameScore::from('RecordGameScore as a')
            ->select('a.*', 'b.GameID', 'c.name as PlatformName', 'c.alias', 'c.have_order_details', 'd.name as KindName', 'e.ServerName', 'f.room_name', 'g.game_record_id', 'g.created_at')
            ->rightJoin(GameMissingData::tableName() . ' as g', 'a.ID', '=', 'g.game_record_id')
            ->leftJoin(AccountsInfo::tableName() . ' as b', 'a.UserID', '=', 'b.UserID')
            ->leftJoin(OuterPlatform::tableName() . ' as c', 'a.PlatformID', '=', 'c.id')
            ->leftJoin(OuterPlatformGame::tableName() . ' as d', function ($join) {
                $join->on('a.KindID', '=', 'd.kind_id')->on('a.PlatformID', '=', 'd.platform_id');
            })
            ->leftJoin(GameRoomInfo::tableName() . ' as e', function ($join) {
                $join->on('a.ServerID', '=', 'e.ServerID')->where('a.PlatformID', '<', 2000);//查询官方平台的
            })
            ->leftJoin(GameRoom::tableName() . ' as f', function ($join) {
                $join->on('d.id', '=', 'f.platform_gameid')->on('a.ServerID', '=', 'f.room_id');//查询官方平台的
            })
            ->andFilterBetweenWhere('g.created_at', request('miss_start_date'), request('miss_end_date'))
            ->andFilterBetweenWhere('a.OrderTime', request('start_date'), request('end_date'))
            ->andFilterWhere('a.PlatformID', request('platform_id'))
            ->orderBy('a.ID', 'desc')
            ->paginate($page_list_rows);

        return $this->response->paginator($list, new OuterPlatformGameRecordTransformer());
    }

    //获取大平台列表
    public function getCompany()
    {
        $list = Company::where('id', '>', 1)->select('id', 'name')->get();
        return ResponeSuccess('获取成功', $list);
    }

    //处理遗漏注单
    public function replacementGameRecore()
    {
        \Validator::make(request()->all(), [
            'order_start_time' => 'required|date',
            'order_end_time' => 'required|date',
            'platform' => 'required|integer',
            'interval' => 'required|integer|min:60|max:100',
            'time' => 'required|integer|min:3|max:10',
        ], [
            'order_start_time.required' => '开始日期不能为空',
            'order_start_time.date' => '开始日期无效',
            'order_end_time.required' => '结束日期不能为空',
            'order_end_time.date' => '结束日期无效',
            'platform.required' => '平台不能为空',
            'platform.integer' => '平台为整数',
            'interval.required' => '拉取间隔不能为空',
            'interval.integer' => '拉取间隔为整数',
            'interval.min' => '拉取间隔最小60秒',
            'interval.max' => '拉取间隔最大100秒',
            'time.required' => '拉取区间不能为空',
            'time.integer' => '拉取区间为整数',
            'time.min' => '拉取区间最小3分钟',
            'time.max' => '拉取区间最小10分钟',
        ])->validate();
        //截止时间必须早于当前时间前一个小时
        $time = Carbon::parse(Carbon::now())->diffInSeconds(Carbon::parse(request('order_end_time')), true);
        if ($time <= 3600) {
            return ResponeFails('拉取结束时间最晚为当前时间的前1小时的');
        }
        //拉取时间间隔不能
        $int = Carbon::parse(request('order_start_time'))->diffInSeconds(Carbon::parse(request('order_end_time')), true);
        if ($int > 86400) {
            return ResponeFails('拉取时间不能超过24小时');
        }
        try {
            //不能重复拉取
            $data = GameMissingOrder::where('company_id', request('platform'))
                ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-1 hour")))
                ->where(function ($query) {
                    $query->whereBetween('start_date', [request('order_start_time'), request('order_end_time')])
                        ->orWhere(function ($query) {
                            $query->whereBetween('end_date', [request('order_start_time'), request('order_end_time')]);
                        });
                })
                ->first();
            if (!empty($data)) {
                return ResponeFails('同一平台1小时内不能拉取重复区间的');
            }
            $GameMissingOrder = new GameMissingOrder();
            $GameMissingOrder->company_id = request('platform');
            $GameMissingOrder->start_date = request('order_start_time');
            $GameMissingOrder->end_date = request('order_end_time');
            $GameMissingOrder->duration = request('time');
            $GameMissingOrder->gap = request('interval');
            $GameMissingOrder->save();

            $client = new Client(['base_uri' => config('prots.outer_platform_api')]);
            $data = [
                'platform_id' => request('platform'),
                'start_time' => request('order_start_time'),
                'end_time' => request('order_end_time'),
                'duration' => request('time'),
                'gap' => request('interval')
            ];
            $res = $client->request('POST', '/rpc/platform/missingorder', ['form_params' => $data, 'timeout' => 10]);
            $result = \GuzzleHttp\json_decode($res->getBody());
            if ($result->data->status != 1) {
                return ResponeFails('拉取失败');
            }
            return ResponeSuccess('拉取成功');
        } catch (\Exception $exception) {
            return ResponeFails('拉取失败');
        }
    }


}

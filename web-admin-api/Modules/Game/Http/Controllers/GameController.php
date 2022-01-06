<?php

namespace Modules\Game\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Models\Accounts\MembersInfo;
use Models\Accounts\UserLevel;
use Models\AdminPlatform\GameControlLog;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\OuterPlatform\WashCodeSetting;
use Models\OuterPlatform\WashCodeVip;
use Models\Platform\GameRoomInfo;
use Models\Platform\H5GameKindItem;
use Models\Platform\U3DGameKindItem;
use GuzzleHttp\Client;
use Transformers\GameCategoryRelationTransformer;
use DB;
use Transformers\OuterHotGameTransformer;

class GameController extends Controller
{
    //获取游戏列表
    public function getList($type)
    {
        if ($type == 'H5') {
            $list = H5GameKindItem::orderBy('SortID', 'asc')->get();
            return ResponeSuccess('请求成功', $list);
        } elseif ($type == 'U3D') {
            $list = U3DGameKindItem::orderBy('SortID', 'asc')->get();
            return ResponeSuccess('请求成功', $list);
        }
        return ResponeFails('请求失败');
    }

    //游戏排序
    public function sort($type)
    {
        $info = request()->all();
        if ($type == 'H5') {
            foreach ($info as $k => $v) {
                H5GameKindItem::where('KindID', $v['KindID'])->update(['SortID' => $v['SortID']]);
            }
        } elseif ($type == 'U3D') {
            foreach ($info as $k => $v) {
                U3DGameKindItem::where('KindID', $v['KindID'])->update(['SortID' => $v['SortID']]);
            }
        }
        return ResponeSuccess('操作成功');
    }

    //===================================控制端转换接口========================================
    //设置水位
    public function setwaterlv()
    {
        $model = GameRoomInfo::find(request('ServerID'));
        if (!$model) {
            return ResponeFails('房间不存在');
        }
        try {
            $client = new Client(['base_uri' => getControlUrl()]);
            $res = $client->request('POST', '/api/game/setwaterlv', ['form_params' => request()->all(), 'timeout' => 3]);
            GameControlLog::addOne('设置水位', '修改' . $model->ServerName . '的水位设置，水位值为：' . request('WaterLvRate'));
            return ResponeSuccess('查询成功', \GuzzleHttp\json_decode($res->getBody()));
        } catch (\Exception $exception) {
            return ResponeFails(getMessage($exception->getCode()));
        }
    }


    //重置
    public function resetgameinfo($room_id)
    {
        $model = GameRoomInfo::find($room_id);
        if (!$model) {
            return ResponeFails('房间不存在');
        }
        try {
            $client = new Client(['base_uri' => getControlUrl()]);
            $res = $client->request('POST', '/api/game/resetgameinfo/' . $room_id, ['timeout' => 3]);
            GameControlLog::addOne('重置', '重置' . $model->ServerName . '的设置');
            return ResponeSuccess('查询成功', \GuzzleHttp\json_decode($res->getBody()));
        } catch (\Exception $exception) {
            return ResponeFails(getMessage($exception->getCode()));
        }
    }

    //查询游戏列表
    public function serverlist($kind_id)
    {
        try {
            $client = new Client(['base_uri' => getControlUrl()]);
            $res = $client->request('GET', '/api/game/serverlist/' . $kind_id, ['timeout' => 3]);
            return ResponeSuccess('查询成功', \GuzzleHttp\json_decode($res->getBody()));
        } catch (\Exception $exception) {
            return ResponeFails(getMessage($exception->getCode()));
        }
    }

    //查询游戏所有信息
    public function gameinfo($room_id)
    {
        try {
            $client = new Client(['base_uri' => getControlUrl()]);
            $res = $client->request('GET', '/api/game/gameinfo/' . $room_id, ['form_params' => request()->all(), 'timeout' => 3]);
            return ResponeSuccess('查询成功', \GuzzleHttp\json_decode($res->getBody()));
        } catch (\Exception $exception) {
            return ResponeFails(getMessage($exception->getCode()));
        }
    }

    //设置彩金信息
    public function setcaijin($room_id)
    {
        $model = GameRoomInfo::find($room_id);
        if (!$model) {
            return ResponeFails('房间不存在');
        }
        try {
            $client = new Client(['base_uri' => getControlUrl()]);
            $res = $client->request('POST', '/api/game/setcaijin/' . $room_id, ['form_params' => request()->all(), 'timeout' => 3]);
            GameControlLog::addOne('设置彩金', '修改' . $model->ServerName . '的彩金设置');
            return ResponeSuccess('查询成功', \GuzzleHttp\json_decode($res->getBody()));
        } catch (\Exception $exception) {
            return ResponeFails(getMessage($exception->getCode()));
        }
    }

    //跑马灯设置
    public function systemmessage($room_id)
    {
        $model = GameRoomInfo::find($room_id);
        if (!$model) {
            return ResponeFails('房间不存在');
        }
        if (request()->isMethod('get')) {
            return ResponeSuccess('查询成功', $model);
        } elseif (request()->isMethod('post')) {
            try {
                $model->MessageMinScore = request('min_score');
                $model->Frequence = request('frequence');
                $model->CycleCount = request('count');
                if (!$model->save()) {
                    return ResponeFails('设置失败');
                }
                GameControlLog::addOne('跑马灯设置', '修改' . $model->ServerName . '的跑马灯设置');
                $client = new Client(['base_uri' => getControlUrl()]);
                $client->request('POST', '/api/game/systemmessage/' . $room_id, ['form_params' => request()->all(), 'timeout' => 3]);
                return ResponeSuccess('操作成功');
            } catch (\Exception $exception) {
                return ResponeFails(getMessage($exception->getCode()));
            }
        }
    }

    //分类设置信息
    public function categoryInfo()
    {
        $list = GameCategory::orderBy('sort')->get()->toArray();
        if (count($list) < 4) {
            $config = config('game_category');
            foreach ($config as $item) {
                GameCategory::firstOrCreate(['name' => $item['name']]);
            }
            $list = GameCategory::orderBy('sort')->get()->toArray();
        }
        return ResponeSuccess('请求成功', $list);
    }

    //分类设置编辑
    public function categoryEdit()
    {
        $category = request('category');
        if (!$category) {
            return ResponeFails('分类数据不能为空');
        }
        GameCategory::beginTransaction([GameCategory::connectionName()]);
        foreach ($category as $item) {
            if (!$item['id'] || !$item['sort']) {
                GameCategory::rollBack([GameCategory::connectionName()]);
                return ResponeFails('分类数据缺少');
            }
            if ($item['sort'] < 1) {
                return ResponeFails('排序值最小为1');
            }
            if ($item['sort'] > 100) {
                return ResponeFails('排序值不能大于100');
            }
            GameCategory::where('id', $item['id'])->update(['sort' => $item['sort']]);
        }
        GameCategory::commit([GameCategory::connectionName()]);
        return ResponeSuccess('操作成功');
    }

    //游戏分类中平台列表
    public function gameCategoryList()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required'],
            'platform_name' => ['nullable'],
            'status' => ['nullable', 'in:0,1,2'],
            'server_status' => ['nullable', 'in:0,1,2'],
        ], [
            'category_id.required' => '游戏分类id必传',
            'status.in' => '平台状态值不在范围内',
            'server_status.in' => '平台维护状态值不在范围内',
        ])->validate();
        $list = GameCategoryRelation::andFilterWhere('category_id', request('category_id'))
            ->whereHas('platform', function ($platform) {
                if (request('platform_name')) {
                    $platform->where('name', 'like', '%' . request('platform_name') . '%');
                }
                if (request('status')) {
                    $platform->where('status', request('status'));
                }
                if (request('server_status')) {
                    $platform->where('server_status', request('server_status'));
                }
            })->orderBy('sort')->orderBy('id')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new GameCategoryRelationTransformer());
    }

    //游戏分类中平台列表编辑
    public function platformEdit()
    {
        \Validator::make(request()->all(), [
            'platform_id' => ['required', 'integer'],
            'description' => ['required'],
            'sort' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:1,2'],
            'server_status' => ['required', 'in:1,2'],
        ], [
            'platform_id.required' => '平台id必传',
            'description.required' => '请填写平台简介',
            'sort.required' => '请输入排序值',
            'sort.integer' => '排序值必须为数字',
            'sort.min' => '排序值最小为1',
            'status.required' => '请选择状态',
            'status.in' => '平台状态值不在范围内',
            'server_status.in' => '平台维护状态值不在范围内',
        ])->validate();
        $model = GameCategoryRelation::where('platform_id', request('platform_id'))->first();  //关系表
        if (!$model) {
            return ResponeFails('操作失败');
        }
        $model->sort = request('sort', 1);
        $res = $model->save();
        $platform = OuterPlatform::where('id', request('platform_id'))->first();
        if ($res && $platform) {
            $platform->description = request('description', '');
            $platform->icon = request('icon', '');
            $platform->web_icon = request('web_icon', '');
            $platform->icons = request('icons', '');
            $platform->img = request('img', '');
            $platform->status = request('status', 1);
            $platform->server_status = request('server_status', 1);
            if ($platform->save()) {
                return ResponeSuccess('操作成功');
            }
        }
        return ResponeFails('操作失败');
    }

    //游戏分类中游戏列表编辑
    public function gameCategoryEdit()
    {
        \Validator::make(request()->all(), [
            'sort' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:1,2'],
        ], [
            'sort.required' => '请输入排序值',
            'sort.integer' => '排序值必须为数字',
            'sort.min' => '排序值最小为1',
            'status.required' => '请选择状态'
        ])->validate();
        $model = OuterPlatformGame::where('id', request('id'))->first();
        if (!$model) {
            return ResponeFails('操作失败');
        }
        $model->icon = request('icon', '');
        $model->img = request('img', '');
        $model->sort = request('sort', 0);
        $model->status = request('status', 1);
        if (!$model->save()) {
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }
    //游戏分类删除
    /*public function gameCategoryDel(){
        if(!request('id')){
            return ResponeFails('操作失败');
        }
        $res = GameCategoryRelation::query()->where('id',request('id'))->get()
            ->each(function($model) {
                $model->delete();
            });
        if(!$res){
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }*/

    //根据平台ID获取游戏
    public function getPlatformGame()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
            'platform_id' => ['required', 'integer'],
        ])->validate();
        $hot_field = OuterPlatformGame::getSortField(request('category_id'));
        if (!$hot_field) {
            return ResponeFails('该分类不存在');
        }
        $list = OuterPlatformGame::select('kind_id as id', 'name', 'icon')->where('platform_id', request('platform_id'))->where($hot_field, 0)->orderBy('sort')->get()->toArray();
        return ResponeSuccess('获取成功', $list);
    }

    //批量操作平台
    public function setBulkPlatform()
    {
        \Validator::make(request()->all(), [
            'status' => ['required', 'integer', 'in:1,2'],
            'platform_ids' => ['required', 'array'],
        ])->validate();
        if (OuterPlatform::saveBulkAction(request('platform_ids'))) {
            return ResponeSuccess('操作成功');
        }
        return ResponeSuccess('操作失败');
    }

    //批量操作游戏状态
    public function setBulkGameCategory()
    {
        \Validator::make(request()->all(), [
            'status' => ['required', 'integer', 'in:1,2'],
            'ids' => ['required', 'array'],
        ])->validate();
        if (OuterPlatformGame::saveBulkAction(request('ids'))) {
            return ResponeSuccess('操作成功');
        }
        return ResponeSuccess('操作失败');
    }

    /**
     * 获取热门分类
     */
    public function getHotCategory()
    {
        $hotCategory = GameCategory::where('tag', GameCategory::HOT_TAG)->orderBy('sort', 'asc')->get();
        return ResponeSuccess('获取成功', $hotCategory);
    }


    /**
     *获取热门游戏列表
     */
    public function getHotCategoryList()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
            'status' => ['nullable', 'in:1,2'],
        ], [
            'category_id.required' => '热门分类ID必传'
        ])->validate();
        $hot_field = OuterPlatformGame::getSortField(request('category_id'));
        if (!$hot_field) {
            return ResponeFails('该热门分类不存在');
        }
        $list = OuterPlatformGame::query()->select('*', DB::raw($hot_field . ' as HotSort'))->with('platform')
            ->whereHas('platform', function ($query) {
                $query->andFilterWhere('name', 'like', '%' . request('platform_name') . '%');
            })
            ->andFilterWhere('name', 'like', '%' . request('game_name') . '%')
            ->andFilterWhere('status', request('status'))
            ->where($hot_field, '>', 0)
            ->orderBy($hot_field, 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(config('page.list_rows'));
        $max_sort = OuterPlatformGame::where($hot_field, '>', 0)->max($hot_field);
        return $this->response->paginator($list, new OuterHotGameTransformer())->addMeta('max_sort', $max_sort);
    }

    /**
     * 新增热门分类下的游戏
     */
    public function addHotGame()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
            'platform_id' => ['required', 'integer'],
            'kind_sorts.*.kind_id' => ['required', 'integer'],
            'kind_sorts.*.sort' => ['required', 'integer'],
            'kind_sorts' => ['required', 'array'],
        ], [
            'category_id.required' => '请选择热门分类ID',
            'category_id.integer' => '热门分类ID为整数',
            'platform_id.required' => '请选择平台',
            'platform_id.integer' => '平台ID为整数',
            'kind_sorts.*.kind_id.required' => '游戏ID为必传',
            'kind_sorts.*.kind_id.integer' => '游戏ID为整数',
            'kind_sorts.*.sort.required' => '排序值为必传',
            'kind_sorts.*.sort.integer' => '排序值为整数',
            'kind_sorts.required' => '分类游戏必填',
            'kind_sorts.array' => '分类游戏数据为数组格式',
        ])->validate();
        $kind_sorts = request('kind_sorts');
        $platform_id = request('platform_id');
        $hot_field = OuterPlatformGame::getSortField(request('category_id'));
        if (!$hot_field) {
            return ResponeFails('该热门分类不存在');
        }
        if (!OuterPlatform::find($platform_id)) {
            return ResponeFails('操作失败,该游戏平台不存在');
        }
        try {
            $more = [];
            foreach ($kind_sorts as $kind_sort) {
                $kind_id = $kind_sort['kind_id'] ?? '';
                $sort = $kind_sort['sort'] ?? '';
                $gameName = OuterPlatformGame::select('name')->where(['kind_id' => $kind_id, 'platform_id' => request('platform_id')])->value('name');
                if (!$gameName) {
                    return ResponeFails('操作失败,kind_id:' . $kind_id . '不存在');
                }
                $more[$kind_id] = $sort;
            }
            if (!OuterPlatformGame::AddHotMore($hot_field, $more)) {
                return ResponeFails('添加失败');
            }
        } catch (\Exception $e) {
            return ResponeFails('操作失败,' . $e->getMessage());
        }
        return ResponeSuccess('操作成功');
    }

    /**
     * 编辑热门分类下的游戏
     */
    public function editHotGame()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
            'platform_id' => ['required', 'integer'],
            'kind_id' => ['required', 'integer'],
            'sort' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:1,2'],
        ], [
            'category_id.required' => '请选择热门分类ID',
            'category_id.integer' => '热门分类ID为整数',
            'platform_id.required' => '请选择平台',
            'platform_id.integer' => '平台ID为整数',
            'kind_id.required' => '请选择游戏',
            'sort.required' => '请输入排序值',
            'sort.integer' => '排序值必须为数字',
            'sort.min' => '排序值最小为1',
            'status.required' => '请选择状态'
        ])->validate();
        if (!OuterPlatformGame::EditHotOne()) {
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }

    /**
     * 移除热门游戏
     */
    public function delHotGame()
    {
        \Validator::make(request()->all(), [
            'category_id' => ['required', 'integer'],
            'id' => ['required', 'integer'],
        ], [
            'category_id.required' => '热门分类ID必传',
            'category_id.integer' => '热门分类ID为整数',
            'id.required' => '数据id为必传',
            'id.integer' => '数据id为整数',
        ])->validate();
        if (!OuterPlatformGame::DelHotOne()) {
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }

    //批量操作游戏分类
    public function setBulkHotGame()
    {
        \Validator::make(request()->all(), [
            'status' => ['required', 'integer', 'in:1,2'],
            'ids' => ['required', 'array'],
        ], [
            'status.required' => '启用状态值必传',
            'status.integer' => '启用状态值整数',
            'status.in' => '启用状态值只能是1和2',
            'ids.required' => '至少选一个',
            'ids.array' => '数据传递格式不对',
        ])->validate();
        if (!OuterPlatformGame::saveBulkHotAction()) {
            return ResponeFails('操作失败');
        }
        return ResponeSuccess('操作成功');
    }

    /*
    * 平台excel导入
    * */
    public function import_platforms(Request $request)
    {
        \Validator::make(request()->all(), [
            'import_file' => ['required', 'file', 'mimes:xls,xlsx'],
        ], [
            'import_file.required' => 'excel文件必传',
            'import_file.mimes' => '文件必须是xls, xlsx 类型的excel文件',
        ])->validate();
        $excel_file_path = $request->file('import_file');//接受文件路径
        $datas = Excel::toArray(new OuterPlatform(), $excel_file_path);
        $list = $datas[0] ?? [];
        if (count($list) < 2) {
            return ResponeFails('导入缺少数据');
        }
        unset($list[0]);
        if (count($list) > 1000) {
            return ResponeFails('导入数据不能超过1000条');
        }
        $id_column = array_column($list, 0);
        if (count($id_column) != count(array_unique($id_column))) {
            return ResponeFails('平台id有重复值');
        }
        $alias_column = array_column($list, 3);
        if (count($alias_column) != count(array_unique($alias_column))) {
            return ResponeFails('平台标识有重复值');
        }
        $data = [];
        $relation = [];
        foreach ($list as $k => $item) {
            $is_exit = OuterPlatform::where('id',$item[0])->first();
            if($is_exit){
                return ResponeFails('平台已导入');
            }
            if (count($item) < 7)
                return ResponeFails('平台编号:' . $item[0] . ',该行缺少数据');
            if (!$item[1])
                return ResponeFails('平台编号:' . $item[0] . ',该行缺少平台名称');
            if (!$item[2])
                return ResponeFails('平台编号:' . $item[0] . ',该行缺少平台描述');
            if (!$item[3])
                return ResponeFails('平台编号:' . $item[0] . ',该行缺少平台标识');
            if (!$item[4] || !is_numeric($item[4]) || $item[4] < 0)
                return ResponeFails('平台编号:' . $item[0] . ',该行公司id不正确');
            if ($item[5] < 0 || $item[5] > 1)
                return ResponeFails('平台编号:' . $item[0] . ',该行是否有游戏的字段值不正确');
            if (!$item[6])
                return ResponeFails('平台编号:' . $item[0] . ',该行缺少游戏分类的id');
            $data[] = [
                'id' => $item[0],
                'name' => $item[1],
                'description' => $item[2],
                'alias' => $item[3],
                'company_id' => $item[4],
                'have_games' => $item[5],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $relation[] = [
                'category_id' => $item[6],
                'platform_id' => $item[0],
                'kind_id' => 0,
                'status' => 1,
                'sort' => 0,
            ];
        }
        $db_outer_platform = \DB::connection('outer_platform');
        $db_outer_platform->beginTransaction();
        try {
            $res1 = OuterPlatform::insert($data);
            $res2 = GameCategoryRelation::insert($relation);
            if (!($res1 && $res2)) {
                $db_outer_platform->rollback();
                return ResponeFails('插入失败');
            }
            $db_outer_platform->commit();
            return ResponeSuccess('操作成功');
        } catch (\Exception $e) {
            $db_outer_platform->rollback();
            return ResponeFails('插入失败:' . $e->getMessage());
        }
    }

    /*
    * 游戏excel导入
    * */
    public function import_games(Request $request)
    {
        //批量导入游戏
        \Validator::make(request()->all(), [
            'import_file' => ['required', 'file', 'mimes:xls,xlsx'],
        ], [
            'import_file.required' => 'excel文件必传',
            'import_file.mimes' => '文件必须是xls, xlsx 类型的excel文件',
        ])->validate();
        $db_outer_platform = \DB::connection('outer_platform');
        $db_outer_platform->beginTransaction();
        try {
            $excel_file_path = $request->file('import_file');//接受文件路径
            $datas = Excel::toArray(new OuterPlatformGame(), $excel_file_path);
            $data = $datas[0] ?? [];
            if (count($data) < 2) {
                return ResponeFails('导入缺少数据');
            }
            unset($data[0]);
            $id_column = array_column($data, 0);
            if (count($id_column) != count(array_unique($id_column))) {
                return ResponeFails('游戏id有重复值');
            }
            //批量存储
            $value = [];
            $count = '';
            foreach ($data as $k => $v) {
                //生成游戏
                $is_exit = OuterPlatformGame::where('id',$v[0])->first();
                if($is_exit){
                    return ResponeFails('游戏已导入');
                }
                $count++;
                //存储表格每行的值
                $value['id']              = (int)$v[0];
                $value['icon']            = $v[1] ?? '';
                $value['name']            = $v[2];
                $value['platform_id']     = (int)$v[3];
                $value['description']     = $v[4] ?? '';
                $value['sort']            = $v[5] ?? '';
                $value['created_at']      = date('Y-m-d H:i:s');
                $value['updated_at']      = date('Y-m-d H:i:s');
                $value['kind_id']         = (int)$v[6];
                $value['platform_alias']  = $v[7];
                $value['game_id']         = $v[8];
                $res1 = OuterPlatformGame::insert($value);
                //生成每个游戏的洗码规则
                $id = $db_outer_platform->table(WashCodeSetting::tableName())->insertGetId([
                    'upper_limit' => $v[9] * getGoldBase(),
                    'category_id' => $v[11],
                    'kind_id'     => (int)$v[6],
                    'platform_id' => (int)$v[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                //vip等级对应的洗码规则
                $vip_lists = MembersInfo::pluck('MemberOrder') ?? [];
                $vipSetting = [];
                foreach ($vip_lists as $key => $item) {
                    $vipSetting[] = [
                        'wash_code_setting_id' => $id,
                        'vip_proportion' => $v[10],
                        'member_order' => $item,
                    ];
                }
                $res2 = WashCodeVip::insert($vipSetting);
                if (!($id && $res1 && $res2)){
                    $db_outer_platform->rollback();
                    return ResponeFails('插入失败');
                }
            }
            $db_outer_platform->commit();
            return ResponeSuccess("操作成功");
        } catch (\Exception $e) {
            $db_outer_platform->rollback();
            return ResponeFails('插入失败:' . $e->getMessage());
        }
    }
}


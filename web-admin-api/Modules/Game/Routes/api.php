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
    $api->group(['prefix' => 'game', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Game\Http\Controllers'], function ($api) {
        //游戏列表
        $api->get('list/{type}', ['uses' => 'GameController@getList', 'permission' => ['admin', '游戏列表', '游戏控制', 'v1']])->name('game.list');
        //游戏排序
        $api->put('sort/{type}', ['uses' => 'GameController@sort', 'permission' => ['admin', '游戏排序', '游戏控制', 'v1']])->name('game.sort');

        //设置水位
        $api->post('setwaterlv', ['uses' => 'GameController@setwaterlv', 'permission' => ['admin', '设置水位', '游戏控制', 'v1']])->name('game.setwaterlv');
        //重置
        $api->post('resetgameinfo/{room_id}', ['uses' => 'GameController@resetgameinfo', 'permission' => ['admin', '重置', '游戏控制', 'v1']])->name('game.resetgameinfo');
        //查询游戏列表
        $api->get('serverlist/{kind_id}', ['uses' => 'GameController@serverlist', 'permission' => ['admin', '查询游戏列表', '游戏控制', 'v1']])->name('game.serverlist');
        //查询游戏所有信息
        $api->get('gameinfo/{room_id}', ['uses' => 'GameController@gameinfo', 'permission' => ['admin', '查询游戏所有信息', '游戏控制', 'v1']])->name('game.gameinfo');
        //设置彩金信息
        $api->post('setcaijin/{room_id}', ['uses' => 'GameController@setcaijin', 'permission' => ['admin', '设置彩金信息', '游戏控制', 'v1']])->name('game.setcaijin');
        //跑马灯设置
        $api->match(['get', 'post'], 'systemmessage/{room_id}', ['uses' => 'GameController@systemmessage', 'permission' => ['admin', '跑马灯设置', '游戏控制', 'v1']])->name('game.systemmessage');
        //防刷检测,玩家信息
        $api->get('checkPlayer', ['uses' => 'PlayerController@index', 'permission' => ['admin','玩家信息','防刷检测','v1']])->name('game.check.player');
        //防刷检测,玩家信息
        $api->get('checkIp', ['uses' => 'PlayerController@checkIp', 'permission' => ['admin','检测IP','防刷检测','v1']])->name('game.check.ip');
        //防刷检测,玩家信息
        $api->get('checkArea', ['uses' => 'PlayerController@checkArea', 'permission' => ['admin','检测地区','防刷检测','v1']])->name('game.check.area');

        //分类设置信息
        $api->get('category/info', ['uses' => 'GameController@categoryInfo', 'permission' => ['admin', '分类设置信息', '分类设置', 'v1']])->name('game.category.info');
        //分类设置编辑
        $api->post('category/edit', ['uses' => 'GameController@categoryEdit', 'permission' => ['admin', '分类设置编辑', '分类设置', 'v1']])->name('game.category.edit');
        //游戏分类列表
        $api->get('game_category/list', ['uses' => 'GameController@gameCategoryList', 'permission' => ['admin', '平台列表', '游戏分类', 'v1']])->name('game.game_category.list');
        //游戏分类平台编辑
        $api->post('platform/edit', ['uses' => 'GameController@platformEdit', 'permission' => ['admin', '平台编辑', '游戏分类', 'v1']])->name('game.platform.edit');
        //游戏分类游戏编辑
        $api->post('game_category/edit', ['uses' => 'GameController@gameCategoryEdit', 'permission' => ['admin', '游戏编辑', '游戏分类', 'v1']])->name('game.game_category.edit');
        //游戏分类删除
       // $api->post('game_category/del', ['uses' => 'GameController@gameCategoryDel', 'permission' => ['admin', '游戏分类删除', '游戏分类', 'v1']])->name('game.game_category.del');
        //根据平台ID获取游戏
        $api->get('getPlatformGame', ['uses' => 'GameController@getPlatformGame'])->name('game.get_platform_game');
        //批量修改游戏平台
        $api->post('game_platform/set_bulk', ['uses' => 'GameController@setBulkPlatform', 'permission' => ['admin', '批量修改平台状态', '游戏分类', 'v1']])->name('game.game_platform.set_bulk');
        //批量修改游戏状态
        $api->post('game_category/set_bulk', ['uses' => 'GameController@setBulkGameCategory', 'permission' => ['admin', '批量修改游戏状态', '游戏分类', 'v1']])->name('game.game_category.set_bulk');

        //获取热门分类
        $api->get('getHotCategory', ['uses' => 'GameController@getHotCategory']);
        //获取热门游戏列表
        $api->get('getHotCategoryList', ['uses' => 'GameController@getHotCategoryList', 'permission' => ['admin', '热门游戏列表', '热门游戏', 'v1']])->name('game.hot_category_list');
        //新增热门分类下的游戏
        $api->post('addHotGame', ['uses' => 'GameController@addHotGame', 'permission' => ['admin', '新增热门游戏', '热门游戏', 'v1']])->name('game.add_hot_game');
        //编辑热门分类下的游戏
        $api->post('editHotGame', ['uses' => 'GameController@editHotGame', 'permission' => ['admin', '编辑热门游戏', '热门游戏', 'v1']])->name('game.edit_hot_game');
        //移除热门分类下的游戏
        $api->post('delHotGame', ['uses' => 'GameController@delHotGame', 'permission' => ['admin', '移除热门游戏', '热门游戏', 'v1']])->name('game.del_hot_game');
        //批量操作热门游戏
        $api->post('setBulkHotGame', ['uses' => 'GameController@setBulkHotGame', 'permission' => ['admin', '批量操作游戏分类', '热门游戏', 'v1']])->name('game.set_bulk_hot');
        //导入平台和游戏分类关系表
        $api->post('importPlatforms', ['uses' => 'GameController@import_platforms', 'permission' => ['admin', '导入平台', '游戏分类', 'v1']])->name('game.import_platforms');
        //导入游戏和对应的洗码规则
        $api->post('importGames', ['uses' => 'GameController@import_games', 'permission' => ['admin', '导入平台', '游戏分类', 'v1']])->name('game.import_games');
    });

});


<?php

namespace Modules\Client\Http\Controllers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Models\Accounts\AccountsBinding;
use Models\AdminPlatform\GameHelps;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\GameCategoryRelation;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\Treasure\RecordDrawScoreForWeb;
use App\Http\Controllers\Controller;
class GameController extends Controller
{
    /**
     * 获取用户游戏记录（30条记录）
     *
     * @param       $UserID         int             用户id，必填项
     * @param       $KindID         int             游戏id，必填项
     *
     */
    public function getRecordDrawScoreForWeb(Request $request)
    {
        //参数检测
        if (!$request->has(['UserID', 'KindID'])) {
            return ResponeFails('缺少参数');
        }
        $field = ['DrawID','UserID','KindID','Score','InsertTime','ServerLevel'];
        $list = RecordDrawScoreForWeb::where('UserID',request('UserID'))
            ->where('KindID',request('KindID'))
            ->where('InsertTime','>=',Carbon::today()->toDateTimeString())
            ->orderBy('DrawID','desc')->limit(10)->select($field)->get();
        foreach ($list as $k => $v){
            $list[$k]['InsertTime'] = date('Y-m-d H:i',strtotime($v['InsertTime']));
        }
        return ResponeSuccess('查询成功',$list);
    }

    /**
     * 获取游戏下载的二维码
     *
     * @param       $user_id        int         用的的id
     *
     */
    public function getQrcode()
    {
        $user_id = request('user_id');
        $type = request('type','app');
        $url = getQrcodeUrl($type).'?agentid='.$user_id;
        $options = new QROptions([
            'version'      => 7,//版本号
            'outputType'   => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel'     => QRCode::ECC_L,//错误级别
            'scale'        => 10,//像素大小
            'imageBase64'  => false,//是否将图像数据作为base64或raw来返回
        ]);
        header('Content-type: image/jpeg');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        echo (new QRCode($options))->render($url);
    }


    //================代理渠道绑定
    public function accountsBinding()
    {
        try{
            if (!AccountsBinding::saveOne()){
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        }catch (\Exception $exception){
            return ResponeFails('操作失败');
        }
    }

    //游戏分类
    public function gameCategory(){
        $list = GameCategory::with(['relation'=>function($query){
            $query->orderBy('sort')->orderBy('id','desc')->with(['platform'=>function($query){
                $query->where('status',OuterPlatform::STATUS_ON)->orderBy('sort');
            }]);
        }])->where('status',GameCategory::STATUS_ON)->orderBy('sort')->get();
        $data = [];
        foreach ($list as $k => $v){
            $data[$k]['category_id'] = $v['id'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['compose_type'] = $v['compose_type'];
            $data[$k]['icons_url'] = !empty( $v['icon']) ? cdn( $v['icon']) : '';
            $data[$k]['list'] = [];

            //判断是否标签或游戏分类
            if ($v->tag == 1 || $v->show_platform == 0){
                $games = [];
                if ($v->tag == 1){
                    //查询具有当前分类的热门游戏
                    $hots = OuterPlatformGame::from('outer_platform_game as a')
                        ->select('a.platform_id','a.name','a.kind_id','a.kind_id','a.type','a.server_status','a.icon','a.icons','b.img','a.hot_sort','a.ehot_sort','a.qhot_sort','b.server_status as platform_server_status','b.company_id','b.alias')
                        ->leftJoin(OuterPlatform::tableName().' as b','a.platform_id','=','b.id')
                        ->where('a.status',OuterPlatformGame::STATUS_ON)
                        ->where('b.status',OuterPlatform::STATUS_ON);
                    if ($v->id == GameCategory::HOT_SORT){
                        $hots = $hots->where('a.hot_sort','>',0)->orderBy('a.hot_sort','asc')->orderBy('a.created_at','desc');
                    }elseif ($v->id == GameCategory::EHOT_SORT){
                        $hots = $hots->where('a.ehot_sort','>',0)->orderBy('a.ehot_sort','asc')->orderBy('a.created_at','desc');
                    }elseif ($v->id == GameCategory::QHOT_SORT){
                        $hots = $hots->where('a.qhot_sort','>',0)->orderBy('a.qhot_sort','asc')->orderBy('a.created_at','desc');
                    }
                    $games = $hots->get();
                }elseif ($v->show_platform == 0){
                    //查询分类游戏
                    $games = GameCategoryRelation::from('game_category_relation as a')
                        ->select('b.company_id','b.alias','b.have_games','b.server_status as platform_server_status','c.platform_id','c.name','c.kind_id','c.kind_id','c.type','c.server_status','c.icon','c.icons','c.img')
                        ->join(OuterPlatform::tableName().' as b','a.platform_id','=','b.id')
                        ->join(OuterPlatformGame::tableName().' as c','a.platform_id','=','c.platform_id')
                        ->where('a.category_id',$v->id)
                        ->where('a.status',GameCategoryRelation::STATUS_ON)
                        ->where('b.status',OuterPlatform::STATUS_ON)
                        ->where('c.status',OuterPlatformGame::STATUS_ON)
                        ->orderBy('a.sort')
                        ->orderBy('a.id')
                        ->get();
                }
                foreach ($games as $key => $val){
                    //平台或游戏维护都算维护
                    if ($val['platform_server_status'] == OuterPlatform::SERVER_STATUS_OFF || $val['server_status'] == OuterPlatformGame::SERVER_STATUS_OFF){
                        $server_status = OuterPlatformGame::SERVER_STATUS_OFF;
                    }else{
                        $server_status = OuterPlatformGame::SERVER_STATUS_ON;
                    }
                    $data[$k]['list'][$key]['name']             = $val['name'];
                    $data[$k]['list'][$key]['company_id']       = $val['company_id'];
                    $data[$k]['list'][$key]['platform_id']      = $val['platform_id'];
                    $data[$k]['list'][$key]['platform_alias']   = $val['alias'];
                    $data[$k]['list'][$key]['kind_id']          = $val['kind_id'];
                    $data[$k]['list'][$key]['type']             = (int)$val['type'];
                    $data[$k]['list'][$key]['server_status']    = $server_status;
                    $data[$k]['list'][$key]['icon_url']         = !empty( $val['icon']) ? cdn( $val['icon']) : '';
                    $data[$k]['list'][$key]['icons_url']        = !empty( $val['icons']) ? cdn( $val['icons']) : '';
                    $data[$k]['list'][$key]['img_url']          = !empty( $val['img']) ? cdn( $val['img']) : '';
                    $data[$k]['list'][$key]['have_games']       = 0;
                }
            }else{
                $i = 0;
                foreach ($v->relation as $key => $val){
                    if (empty($val['platform'])){
                        continue;
                    }
                    $data[$k]['list'][$i]['name']             = $val['platform']['name'];
                    $data[$k]['list'][$i]['company_id']       = $val['platform']['company_id'];
                    $data[$k]['list'][$i]['platform_id']      = $val['platform_id'];
                    $data[$k]['list'][$i]['platform_alias']   = $val['platform']['alias'];
                    $data[$k]['list'][$i]['kind_id']          = $val['kind_id'];
                    $data[$k]['list'][$i]['type']             = 0;
                    $data[$k]['list'][$i]['server_status']    = $val['platform']['server_status'];
                    $data[$k]['list'][$i]['icon_url']         = !empty( $val['platform']['icon']) ? cdn( $val['platform']['icon']) : '';
                    $data[$k]['list'][$i]['icons_url']        = !empty( $val['platform']['icons']) ? cdn( $val['platform']['icons']) : '';
                    $data[$k]['list'][$i]['img_url']          = !empty( $val['platform']['img']) ? cdn( $val['platform']['img']) : '';
                    $data[$k]['list'][$i]['have_games']       = $val['platform']['have_games'];
                    $i++;
                }
            }
        }
        return ResponeSuccess('查询成功',$data);
    }

    /**
     * 获取游戏分类
     *
     */
    public function subGameCategory()
    {
        $list = OuterPlatformGame::from('outer_platform_game as a')
            ->select('a.id','a.platform_id','a.name','a.kind_id','a.kind_id','a.type','a.server_status','a.icon','a.icons','b.img','b.server_status as platform_server_status','b.company_id','b.alias')
            ->leftJoin(OuterPlatform::tableName().' as b','a.platform_id','=','b.id')
            ->where('a.platform_id',request('platform_id'))
            ->where('a.status',OuterPlatformGame::STATUS_ON)
            ->where('b.status',OuterPlatform::STATUS_ON)
            ->orderBy('a.sort','asc')
            ->orderBy('a.created_at','desc')
            ->get();
        $data = [];
        foreach ($list as $k => $v){
            if ($v['platform_server_status'] == OuterPlatform::SERVER_STATUS_OFF || $v['server_status'] == OuterPlatformGame::SERVER_STATUS_OFF){
                $server_status = OuterPlatformGame::SERVER_STATUS_OFF;
            }else{
                $server_status = OuterPlatformGame::SERVER_STATUS_ON;
            }

            $data[$k]['name']             = $v['name'];
            $data[$k]['company_id']       = $v['company_id'];
            $data[$k]['platform_id']      = $v['platform_id'];
            $data[$k]['platform_alias']   = $v['alias'];
            $data[$k]['kind_id']          = $v['kind_id'];
            $data[$k]['type']             = $v['type'];
            $data[$k]['server_status']    = $server_status;
            $data[$k]['icon_url']         = !empty( $v['icon']) ? cdn( $v['icon']) : '';
            $data[$k]['icons_url']        = !empty( $v['icons']) ? cdn( $v['icons']) : '';
            $data[$k]['img_url']          = !empty( $v['img']) ? cdn( $v['img']) : '';
            $data[$k]['have_games']       = 0;
        }
        return ResponeSuccess('查询成功',$data);
    }
}

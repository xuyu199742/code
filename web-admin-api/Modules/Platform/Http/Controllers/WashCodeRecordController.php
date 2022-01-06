<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2020/3/27
 * Time: 14:51
 */
namespace Modules\Platform\Http\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Models\Accounts\SystemStatusInfo;
use Models\OuterPlatform\GameCategoryRelation;
use Modules\Platform\Http\Requests\SetWashCodeStartTimeSettingRequest;
use function foo\func;
use Models\Accounts\AccountsInfo;
//use Models\Accounts\SystemStatusInfo;
use Models\OuterPlatform\GameCategory;
use Models\OuterPlatform\WashCodeHistory;
use Models\OuterPlatform\OuterPlatform;
use Models\OuterPlatform\OuterPlatformGame;
use Models\OuterPlatform\WashCodeRecord;
use Models\OuterPlatform\WashCodeSetting;
use Models\Treasure\RecordGameScore;
use Modules\Platform\Http\Requests\WashCodeRecordRequest;
use Transformers\WashCodeRecordTransformer;
use DB;

class WashCodeRecordController extends Controller
{
    /**
     * 获取洗码记录
     */
    public function getList(WashCodeRecordRequest $request){
        $start_time = request('start_date');
        $end_time = request('end_date');
        $list = WashCodeHistory::from(WashCodeHistory::tableName().' as a')
            ->selectRaw('a.id,a.user_id,a.created_at,
                max(b.aggregate_wash_code) as aggregate_wash_code,
                sum(b.jetton_score) as jetton_score,
                sum(b.wash_code) as wash_code,
                max(b.jetton_total_score) as jetton_total_score'
            )
            ->leftJoin(WashCodeRecord::tableName().' as b','a.id','=','b.wash_code_history_id')
            ->andFilterBetweenWhere('a.created_at',$start_time,$end_time)
            ->andFilterWhere('b.game_id',request('game_id'))
            ->groupBy('a.user_id','a.created_at','a.id')
            ->orderBy('a.created_at','desc')
            ->paginate(config('page.list_rows'));
        $default_value = SystemStatusInfo::query()->where('StatusName', 'WashCodeStartTime')->value('StatusValue');
        return $this->response->paginator($list,new WashCodeRecordTransformer())->addMeta('default_value', date('Y-m-d H:i:s', $default_value));
    }

    /**
     * 获取洗码详情
     */
    public function getDetail(WashCodeRecordRequest $request){
        $history = WashCodeHistory::find($request->record_id);
        if(!$history){
            return ResponeFails('该领取记录不存在');
        }
        $GameID = AccountsInfo::find($history->user_id)->GameID ?? '';
        $StatusValue = SystemStatusInfo::where('StatusName','WashCodeStartTime')->value('StatusValue');
        $setting_at = date('Y-m-d H:i:s',$StatusValue ?: time());
        $created_at = $history->created_at;
        $prev_at = WashCodeHistory::where('created_at','<',$created_at)->where('user_id',$history->user_id)->orderBy('created_at','desc')->value('created_at');
        $joinSub = DB::table(RecordGameScore::tableName().' as a')
            ->select('a.KindID','a.PlatformID','a.UserID',
                DB::raw('(select top 1 name from '.OuterPlatformGame::tableName().' where a.KindID = kind_id and a.PlatformID = platform_id) as GameName'),
                DB::raw('count(*) as NoteCount'),
                DB::raw('sum(a.JettonScore) as JettonScoreSum'), //有效投注
                DB::raw('sum(a.ChangeScore) as ChangeScoreSum') //玩家输赢
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from(GameCategoryRelation::tableName().' as c')
                    ->whereRaw('a.PlatformID = c.platform_id')
                    ->whereRaw('c.category_id = '.request('category_id'));
            })
            ->where('a.UserID',$history->user_id)
            ->where(function($query)use($created_at,$prev_at,$setting_at){
                $query->where('a.UpdateTime','<=',$created_at);
                if($prev_at){
                    $query->where('a.UpdateTime','>',$prev_at);
                }else{
                    $query->where('a.UpdateTime','>',$setting_at);
                }
            })
            ->groupBy('a.KindID','a.PlatformID','a.UserID');
        $list = WashCodeRecord::query()->from(WashCodeRecord::tableName().' as a')
            ->select('a.game_id','a.category_id','a.platform_id','a.kind_id','a.Retio',
                DB::raw('(select top 1 name from '.OuterPlatform::tableName().' where a.platform_id = id) as PlatformName'),
                DB::raw('(select top 1 name from '.OuterPlatformGame::tableName().' where a.kind_id = kind_id and a.platform_id = platform_id) as GameName'),
                DB::raw('sum(a.jetton_score) as JettonScoreSum'), //有效投注
                DB::raw('sum(a.change_score) as ChangeScoreSum'), //玩家输赢
                DB::raw('sum(a.wash_code) as WashCodeSum') //洗码
            )
            ->where('a.game_id' ,$GameID)
            ->where('a.wash_code_history_id',$request->record_id)
            ->where('a.category_id',$request->category_id)
            ->groupBy('a.platform_id','a.kind_id','a.game_id','a.category_id','a.Retio')
            ->get();
        $list = $list->map(function ($item)use($joinSub,$history) {
            $joinSubT = clone $joinSub;
            $query = ['PlatformID' => $item->platform_id,'UserID' => $history->user_id];
            if($item->kind_id > 0){
                $query['KindID'] = $item->kind_id;
            }
            $NoteCount = $joinSubT->where($query)->get()->sum('NoteCount');
            $item->NoteCount = $NoteCount;
            $item->JettonScoreSum =  realCoins($item->JettonScoreSum);
            $item->ChangeScoreSum = realCoins($item->ChangeScoreSum);
            $item->WashCodeSum = realCoins($item->WashCodeSum);
            $item->CodeScoreSum = $item->WashCodeSum;
            return $item;
        });
        $total = [
            'NoteTotal' => $list->sum('NoteCount'),
            'JettonScoreTotal' => bcadd($list->sum('JettonScoreSum'),0,2),
            'ChangeScoreTotal' => bcadd($list->sum('ChangeScoreSum'),0,2),
            'CodeScoreTotal' => bcadd($list->sum('WashCodeSum'),0,2),
        ];
        $list = $list->groupBy('platform_id');
       /* $RetioData = [];
        foreach($list as $item){
            $Retio = $item->groupBy('Retio');
            foreach($Retio as $kt =>  $it){
                $RetioData[$kt] = $it;
             }
        }*/
        $data = [];
        foreach($list as $k => $retioDatum){
            $p = $retioDatum[0];
            $kind_id = $p['kind_id'] ?? 0;
            $data[] = [
                'PlatformID' => $p->platform_id,
                'PlatformName' => $p->PlatformName,
                'PlatformNoteTotal' => $retioDatum->sum('NoteCount'),
                'PlatformJettonScoreTotal' => bcadd($retioDatum->sum('JettonScoreSum'),0,2),
                'PlatformChangeScoreTotal' => bcadd($retioDatum->sum('ChangeScoreSum'),0,2),
                'PlatformCodeScoreTotal' => bcmul($retioDatum->sum('JettonScoreSum') ,$p->Retio / 100,2),
                'PlatformRetio' => $p->Retio,
                'GameList' => $kind_id ? $retioDatum : [],
            ];
        }
        return ResponeSuccess('请求成功',['list' => $data,'total' => $total]);
    }

    //设置领取洗码开始时间
    public function setWashCodeStartTime(SetWashCodeStartTimeSettingRequest $request)
    {
        $system = SystemStatusInfo::query()->updateOrCreate(
            ['StatusName' => 'WashCodeStartTime'],
            [
                'StatusValue' => strtotime($request->start_time),
                'StatusString' => '洗码领取计算开始时间',
                'StatusTip' => '洗码领取计算开始时间',
                'StatusDescription' => '存的是时间戳，取出需要转换',
            ]
        );

        return ResponeSuccess('请求成功', [date('Y-m-d H:i:s', $system->StatusValue)]);
    }
}

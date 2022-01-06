<?php
/*用户游戏房间进出记录*/
namespace Modules\User\Http\Controllers;
use App\Http\Requests\SelectGameIdRequest;
use Illuminate\Http\Request;
use Models\Platform\GameKindItem;
use Models\Platform\GameRoomInfo;
use Models\Treasure\RecordUserInout;
use Transformers\RecordUserInoutTransformer;
use Validator;

class GameInOutController extends BaseController
{
    /**
     * 游戏进出记录
     *
     */
    public function log(SelectGameIdRequest $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable', 'numeric'],
            'kindId'     => ['nullable', 'numeric'],
            'serverId'     => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ])->validate();
        $list = RecordUserInout::from('RecordUserInout as a')->select('a.*','b.GameID','b.NickName')
            ->leftJoin('WHQJAccountsDB.dbo.AccountsInfo AS b','a.UserID','=','b.UserID')
            ->leftJoin(GameRoomInfo::tableName().' as c','a.ServerID','=','c.ServerID')
            ->andFilterBetweenWhere('a.EnterTime',request('start_date'),request('end_date'))
            ->andFilterWhere('a.KindID',request('kindId'))
            ->andFilterWhere('a.ServerID',request('serverId'))
            ->where('b.IsAndroid',0)//玩家
            ->where('c.ServerLevel','>',1)//去除体验场
            ->andFilterWhere('b.GameID',intval(request('game_id')))
            ->orderBy('a.EnterTime','desc')
            ->paginate(config('page.list_rows'));
        $kind=GameKindItem::with('rooms:ServerID,ServerName,KindID')->select('KindID','KindName')->get()->toArray();
        return $this->response->paginator($list,new RecordUserInoutTransformer())->addMeta('kinds',$kind);
    }
}

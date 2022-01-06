<?php
/*游戏*/
namespace Modules\User\Http\Controllers;
use Illuminate\Http\Request;
use Models\Platform\GameKindItem;
use Transformers\GameKindItemTransformer;
class GameController extends BaseController
{

    /**
     * 获取游戏和相关的房间
     *
     * @return \Dingo\Api\Http\Response
     */
    public function gameLinkRoom()
    {
        $list = GameKindItem::orderBy('SortID','asc')->get();
        return $this->response->collection($list,new GameKindItemTransformer());
    }

}

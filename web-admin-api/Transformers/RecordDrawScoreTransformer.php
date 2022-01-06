<?php
/*用户游戏记录*/

namespace Transformers;

use App\Packages\GameFunction\Facades\Gf;
use League\Fractal\TransformerAbstract;
use Models\Treasure\RecordDrawScore;


class RecordDrawScoreTransformer extends TransformerAbstract
{
    public function transform(RecordDrawScore $item)
    {
        //$gf = Gf::format($item);
        return [
            'time'         => $item->InsertTime ? date('Y-m-d H:i:s', strtotime($item->InsertTime)) : '未知',   //时间
            'kind_id'      => $item->KindID, //游戏id
            'game_name'    => $item->KindName ?? '未知', //游戏名
            'room_name'    => $item->ServerName ?? '未知', //房间名
            'draw_id'      => $item->DrawID, //对局标识
            'jetton_score' => realCoins($item->JettonScore ?? 0),//$gf->getJetton(), //总下注量
            'banker'       => (boolean)$item->IsBanker, //是否坐庄
            'cur_score'    => realCoins($item->CurScore ?? 0), //当前携带金币量
            'score'        => realCoins($item->winlose ?? 0),//输赢改为：玩家输赢=中奖金额-投注金额
            'reward_score' => realCoins($item->RewardScore ?? 0),//中奖金额
            'AreaJettons'  => rtrim(trim($item->AreaJettons), ','),//$gf->getArea(), //下注区域
            'CardResult'   => rtrim(trim($item->CardResult), ','),//$gf->getArea(), //牌型
            'BankerCards'  => rtrim(trim($item->BankerCards), ','),//$gf->getArea(), //庄家牌
            'total_draw'   => $item->Waste ? realCoins($item->Waste) : 0, //本局结算
            'divisor'      => realRatio(), //下注换算单位
            'game_id'      => $item -> account->GameID ?? '',
        ];
    }

}

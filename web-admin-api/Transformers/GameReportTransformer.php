<?php
/* 游戏报表*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\SubPermissions;


class GameReportTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        $data = [
            'ServerID'    => $item->ServerID,
               'ServerName'  => $item->ServerName,
               'KindID'      => $item->KindID,
               'OnlineCount' => $item->OnlineCount ?: 0,
               'JettonCount' => $item->JettonCount ?: 0,
               'JettonTotal' => realCoins($item->JettonTotal),
               'SystemTotal' => realCoins($item->SystemTotal),
               'ServiceTotal'=> realCoins($item->ServiceTotal),
               //'CurWaterRate'=> $item->SystemTotal && $item->JettonTotal ? bcdiv($item->SystemTotal*1000,$item->JettonTotal,2) : 0,
               'start_date'  => date('Y-m-d',strtotime(request('start_date',date('Y-m-d')))),
               'end_date'    => date('Y-m-d',strtotime(request('end_date',date('Y-m-d'))))
        ];
        if (SubPermissions::isRule('today_water_level')){
            $data['CurWaterRate'] = $item->SystemTotal && $item->JettonTotal ? bcdiv($item->SystemTotal*1000,$item->JettonTotal,2) : 0;
        }
        return $data;
    }

}
<?php

namespace Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class GameController extends Controller
{

    /**
     * 游戏详情（牌型）
     *
     */
    public function gameDetailsRecord()
    {
        \Validator::make(request()->all(), [
            'platform_id'     => 'required|integer',
            'order_no'        => 'required',
        ], [
            'platform_id.required'  => '平台ＩＤ必传',
            'platform_id.integer'   => '平台ＩＤ为整型',
            'order_no.required'     => '订单号必传',
        ])->validate();


        try{
            $url = env('OUTER_PLATFORM_API', 'http://127.0.0.1:8000');
            $client = new Client(['base_uri' => $url]);
            $param = '?platform_id='.request('platform_id').'&order_no='.request('order_no');
            $res = $client->request('GET', '/rpc/platform/order'.$param, ['timeout' => 10]);
            $result = \GuzzleHttp\json_decode($res->getBody());
            return ResponeSuccess('查询成功',['url'=>$result->data->Url ?? ""]);
        }catch (\Exception $exception){
            return ResponeFails(getMessage($exception->getCode()));
        }
    }

}

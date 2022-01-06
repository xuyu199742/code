<?php
namespace Modules\Payment\Http\Controllers;
use Illuminate\Routing\Controller;
use Models\AdminPlatform\RemitConfig;

class RemitController extends Controller
{
    //回调
    public function callback($type)
    {
        $RemitConfig = RemitConfig::where('notify_tag',$type)->first();
        \Log::channel('daifu_callback')->info($RemitConfig->name.'订单回调数据:'.json_encode(request()->all()));
        $config['mch_id']  = $RemitConfig->mch_id;
        $config['mch_key'] = $RemitConfig->mch_key;
        $BankPay = new $RemitConfig->sdk($config);
        $BankPay->callback();
    }

}

<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\AdminPlatform\PaymentWay;
use Modules\Payment\Packages\ThirdPay\Facades\Pay;
use Validator;

class PaymentSettingController extends Controller
{
    /**
     * 获取所有充值平台
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function platforms()
    {
        return ResponeSuccess('查询成功', Pay::platforms());
    }


    /**
     * 获取单个平台配置
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     *
     */
    public function platform($key)
    {
        return ResponeSuccess('查询成功', Pay::platform($key));
    }

    public function savePlatform()
    {
        if (Pay::savePlatform()) {
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }

    /**
     * 获取充值类型
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function tabs($type)
    {
        $list = PaymentWay::where('type',$type)->get();
        return ResponeSuccess('请求成功',$list);
        //return ResponeSuccess('查询成功', Pay::tabs());
    }

    /**
     * 清理缓存
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function clearCache()
    {
        if (Pay::clearCache()) {
            return ResponeSuccess('清除成功');
        }
        return ResponeFails('清除失败');
    }

    /**
     * 根据支付类型，获取所有支付通道
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function channels()
    {
        $list = Pay::channels();
        return ResponeSuccess('查询成功', $list);
    }

    /**
     * 根据id获取单个通道配置
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function channel($id)
    {
        return ResponeSuccess('查询成功', Pay::channel($id));
    }

    /**
     * 获取下拉框联动
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function selectChannels()
    {
        return ResponeSuccess('查询成功', Pay::canConfigChannels());
    }

    /**
     * 保存通道配置
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function saveChannel()
    {
        if (Pay::saveChannel()) {
            return ResponeSuccess('保存成功', []);
        }
        return ResponeFails('保存失败');
    }


}

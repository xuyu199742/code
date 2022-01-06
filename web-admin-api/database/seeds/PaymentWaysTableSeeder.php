<?php

use Illuminate\Database\Seeder;

class PaymentWaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('admin_platform')->table('payment_ways')->truncate();
        DB::connection('admin_platform')->table('payment_ways')->insert([
                [
                    'name'           => '代理充值',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 0,
                    'pay_type'       => 'official_agent',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '银联转账',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 0,
                    'pay_type'       => 'official_union',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '微信(官方)',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 0,
                    'pay_type'       => 'official_wechat',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '支付宝(官方)',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 0,
                    'pay_type'       => 'official_alipay',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '微信扫码',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'wechat_qecode',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '微信H5',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'wechat_h5',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '支付宝扫码',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'alipay_qecode',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '支付宝H5',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'alipay_h5',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '云闪付',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'yunshanfu',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '银联',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 1,
                    'pay_type'       => 'unionpay_qrcode',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
                [
                    'name'           => '官方充值',
                    'status'         => 'OFF',
                    'sort'           => 0,
                    'marker'         => 0,
                    'type'           => 2,
                    'pay_type'       => 'official',
                    'created_at'     => date('Y-m-d H:i:s', time()),
                    'updated_at'     => date('Y-m-d H:i:s', time()),
                ],
            ]
        );
    }
}

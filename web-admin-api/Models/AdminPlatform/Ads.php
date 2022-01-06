<?php

namespace Models\AdminPlatform;
/*
 *  id                 int               序列id
 *  title              nvarchar          图片标题
 *  resource_url       nvarchar          资源路径
 *  link_url           nvarchar          链接地址
 *  type               tinyint           广告位类型：活动公告1
 *  sort_id            int               排序
 *  remark             nvarchar          备注信息
 *  platform_type      int               平台类型
 *  created_at        datetime           创建时间
 *  updated_at        datetime           更新时间
 */


class Ads extends Base
{
	//数据表
	protected $table = 'ads';
	const ACTIVE_TYPE = 1;  //广告类型：活动公告/精彩活动

	//活动类型
	const WEBSITE = 0; //网址
	const SIGN_IN = 1; //签到
	const SHARE = 2; //分享
	const MATCH = 3; //比赛
	const SHOPPING_MALL = 4; //商城
	const AGENT = 5; //代理
	const RECHARGE = 6; //原充值，现首充
	const TURNTABLE = 7; //转盘
	const RED_PACKET = 8; //红包
	const BIND = 9; //绑定
	const PAYMENT = 10; //充值
	const SERVICE = 11; //客服
    const REBATE = 12; //返利
    const TASK = 13; //任务


	const TYPE = [
		self::WEBSITE       => '网址',
		self::SIGN_IN       => '签到',
		self::SHARE         => '分享',
		self::MATCH         => '比赛',
		self::SHOPPING_MALL => '商城',
		self::AGENT         => '代理',
		self::RECHARGE      => '首充',
		self::TURNTABLE     => '转盘',
		self::RED_PACKET    => '红包',
		self::BIND          => '绑定',
		self::PAYMENT       => '充值',
		self::SERVICE       => '客服',
        self::REBATE        => '返利',
        self::TASK          => '任务',
	];
	const PLATFORM_ALL = 0;//所有
	const PLATFORM_H5 = 1;//H5
	const PLATFORM_U3D = 2;//U3D
	const PLATFORM_TYPE = [
		self::PLATFORM_ALL => '所有',
		self::PLATFORM_H5  => 'H5',
		self::PLATFORM_U3D => 'U3D',
	];

	protected static function boot()
	{
		parent::boot();
		// 监听模型创建事件，在写入数据库之前触发
		static::saving(function ($model) {
			SystemLog::addLogs('修改广告，id为：' . $model->id);
		});
		static::deleting(function ($model) {
			SystemLog::addLogs('删除广告，id为：' . $model->id);
		});
	}
}

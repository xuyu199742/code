<?php

namespace Models\AdminPlatform;


class CarouselAffiche extends Base
{
    //数据表
    protected $table = 'carousel_affiche';

    //广告类型
    const WEBSITE        = 1; //网址
    const SIGN_IN        = 2; //签到
    const AGENT          = 3; //代理
    const RECHARGE       = 4; //充值
    const TURNTABLE      = 5; //转盘
    const RED_PACKET     = 6; //红包
    const BIND           = 7; //绑定
    const SERVICE        = 8; //客服
    const WITHDRAW       = 9; //提现
	const FIRST_RECHARGE = 10;//首充

    const TYPE = [
        self::WEBSITE        => '网址',
        self::SIGN_IN        => '签到',
        self::AGENT          => '代理',
        self::RECHARGE       => '充值',
        self::TURNTABLE      => '转盘',
        self::RED_PACKET     => '红包',
        self::BIND           => '绑定',
        self::SERVICE        => '客服',
        self::WITHDRAW       => '提现',
        self::FIRST_RECHARGE => '首充',
    ];

    public function getTypeTextAttribute()
    {
        return self::TYPE[$this->type ?? self::WEBSITE];//默认网址类型
    }

    public static function saveOne($id = null)
    {
        try{
            $info = request()->all();
            if ($id){
                //编辑
                $model              = self::find($id);
                if (!$model) {
                    return false;
                }
            }else{
                //新增
                $model               = new self();
            }
            $model->type            = $info['type'] ?? 1;
            $model->sort            = $info['sort'] ?? 255;
            $model->image          = $info['image'] ?? '';
            $model->link            = $info['link'] ?? '';
            return $model->save();
        }catch (\Exception $exception){
            return false;
        }
    }

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            SystemLog::addLogs('修改轮播广告，id为：'.$model->id);
        });
        static::deleting(function ($model) {
            SystemLog::addLogs('删除轮播广告，id为：'.$model->id);
        });
    }
}

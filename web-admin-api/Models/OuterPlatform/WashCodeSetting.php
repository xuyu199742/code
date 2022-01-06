<?php

namespace Models\OuterPlatform;

use Models\Accounts\SystemStatusInfo;
use Models\Treasure\RecordGameScore;

class WashCodeSetting extends Base
{
    protected $table = 'wash_code_setting';

    public $guarded = [];

    public $appends = ['lower_limit'];

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            $model->vips()->delete();
        });
    }

    public function vips()
    {
        return $this->hasMany(WashCodeVip::class, 'wash_code_setting_id');
    }

    public function scopePlatform($query, $platform_id, $category_id)
    {
        return $query->where(['platform_id' => $platform_id, 'category_id' => $category_id]);
    }

    public function getLowerLimitAttribute()
    {
        return self::query()->where('upper_limit', '<', $this->upper_limit)
                ->where(['platform_id' => $this->platform_id, 'category_id' => $this->category_id])
                ->orderBy('upper_limit', 'desc')
                ->first()
                ->upper_limit ?? 10000;
    }

    /**
     * 最后一次洗码时间，默认初始配置
     * @param int   $user_id        用户ID
     *
     */
    public function getLastWashCodeDate($user_id)
    {
        $record = WashCodeHistory::where('user_id', $user_id)->orderBy('id', 'desc')->first();
        if (empty($record)){
            $WashCodeStartTime = SystemStatusInfo::where('StatusName', 'WashCodeStartTime')->value('StatusValue');
            $wash_date = date('Y-m-d H:i:s', $WashCodeStartTime);
        }else{
            $wash_date = date('Y-m-d H:i:s',strtotime($record->created_at));
        }
        return $wash_date;
    }

    /**
     * 获取用户未洗码的数据
     * @param int   $user_id        用户ID
     * @param int   $category_id    分类ID
     *
     */
    public function getWashCodeData($user_id, $category_id = null)
    {
        $wash_date = $this->getLastWashCodeDate($user_id);
        $list = $this->from(RecordGameScore::tableName() . ' as a')
            ->selectRaw('b.platform_id, sum(a.JettonScore) as sum_jetton_score, sum(a.ChangeScore) as win_lose, b.category_id, c.name as name')
            ->join(GameCategoryRelation::tableName() . ' as b', 'b.platform_id', '=', 'a.PlatformID')
            ->leftJoin(OuterPlatform::tableName() . ' as c', 'c.id', '=', 'a.PlatformID')
            ->where('a.UserID', $user_id)
            ->where('a.UpdateTime', '>', $wash_date)
            ->andFilterWhere('b.category_id', $category_id)
            ->groupBy('b.platform_id', 'b.category_id', 'c.name')
            ->havingRaw('SUM(a.JettonScore) >= 10000')
            ->get();
        return $list;
    }

    /**
     * 获取当前洗码比例
     * @param int $platform_id       平台ID
     * @param int $sum_jetton_score  投注金额
     * @param int $vip_level         用户vip等级
     *
     * return float                  洗码比例
     */
    public function getCurWashCodeRatio($platform_id, $sum_jetton_score, $vip_level)
    {
        //下限
        $data = $this->with(['vips'=>function($query) use ($vip_level){
            $query->where('member_order','<=',$vip_level)->orderBy('member_order','desc')->first();
        }])
            ->select('id','category_id','platform_id','kind_id','upper_limit')
            ->where('platform_id',$platform_id)
            ->where('upper_limit','>',$sum_jetton_score)
            ->orderBy('upper_limit')
            ->first();
        //上限
        if (empty($data)){
            $data = $this->with(['vips'=>function($query) use ($vip_level){
                $query->where('member_order','<=',$vip_level)->orderBy('member_order','desc')->first();
            }])
                ->select('id','category_id','platform_id','kind_id','upper_limit')
                ->where('platform_id',$platform_id)
                ->orderBy('upper_limit','desc')
                ->first();
        }
        return $data;
    }

    /**
     * 获取用户洗码金额
     * @param int   $user_id        用户ID
     * @param int   $vip_level      用户vip等级
     * @param int   $category_id    分类ID
     *
     */
    public function getWashCodeScore($user_id,$vip_level, $category_id = null)
    {
        $gameRecord = $this->getWashCodeData($user_id, $category_id);
        $wash_code = 0;
        $bet_score = 0;
        foreach ($gameRecord as $k => $v){
            $data = $this->getCurWashCodeRatio($v->platform_id, $v->sum_jetton_score, $vip_level);
            $wash_ratio = $data->vips[0]['vip_proportion'] ?? 0;
            $v->wash_ratio = $wash_ratio;
            $v->wash_score = intval($v->sum_jetton_score * $wash_ratio / 100);
            $wash_code += $v->wash_score;
            $bet_score += $v->sum_jetton_score;
        }
        $list['list'] = $gameRecord;
        $list['wash_code'] = realCoins($wash_code);
        $list['bet_score'] = $bet_score;
        return $list;
    }

    /**
     * 获取各分类洗码比例数据
     *
     */
    public function getCategoryWashCodeRatio()
    {
        $list = GameCategory::with(['relation'=>function($query){
            $query->select('platform_id','category_id')->with(['platform:id,name,icon']);
        }])
            ->select('id','name')
            ->where('tag',GameCategory::GAME_CATEGORY)
            ->get();
        foreach ($list as $key => $val){
            foreach ($val->relation as $k => $v){
                if (!empty($v->platform->icon)){
                    $v->platform->icon =  asset('storage/' . $v->platform->icon);
                }
            }
        }
        return $list;
    }

    /**
     * 获取下级洗码比例
     * @param int $platform_id       平台ID
     * @param int $sum_jetton_score  投注金额
     * @param int $vip_level         用户vip等级
     *
     * return float                  洗码比例
     */
    public function getNextWashCodeRatioLevel($platform_id, $sum_jetton_score, $vip_level)
    {
        //下限
        $data = $this->with(['vips'=>function($query) use ($vip_level){
            $query->where('member_order','<=',$vip_level)->orderBy('member_order','desc')->first();
        }])
            ->select('id','category_id','platform_id','kind_id','upper_limit')
            ->where('platform_id',$platform_id)
            ->where('upper_limit','<=',$sum_jetton_score)
            ->orderBy('upper_limit','desc')
            ->first();
        //上限
        if (empty($data)){
            $data = $this->with(['vips'=>function($query) use ($vip_level){
                $query->where('member_order','<=',$vip_level)->orderBy('member_order','desc')->first();
            }])
                ->select('id','category_id','platform_id','kind_id','upper_limit')
                ->where('platform_id',$platform_id)
                ->orderBy('upper_limit','desc')
                ->first();
        }
        return $data;
    }

}

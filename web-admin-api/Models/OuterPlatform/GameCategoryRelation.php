<?php

namespace Models\OuterPlatform;


class GameCategoryRelation extends Base
{
    protected $table = 'game_category_relation';

    const STATUS_ON   = 1;
    const STATUS_OFF  = 2;
    const STATUS      = [
        self::STATUS_ON  => '启用',
        self::STATUS_OFF => '禁用',
    ];

    public $timestamps = false;

    public $guarded = [];

    //关联分类
    public function category(){
        return $this->belongsTo(GameCategory::class,'category_id','id');
    }

    //关联游戏
    public function game(){
        return $this->belongsTo(OuterPlatformGame::class,'kind_id','kind_id')->whereRaw('[platform_id] = [outer_platform_game].[platform_id]');
    }

    //关联平台
    public function platform(){
        return $this->belongsTo(OuterPlatform::class,'platform_id','id');
    }


    /*单条保存*/
    public static function saveOne($relation_id)
    {
        $model = GameCategoryRelation::where('id',$relation_id)->first();
        if (!$model) {
            $model                 = new self();
            $data = [
                'platform_id' => request('platform_id'),
                'kind_id' => request('game_id')
            ];
            if((GameCategory::find(request('category_id'))->name ?? '') == GameCategory::HOT_CATEGORY){
                $data['category_id'] = request('category_id');
            }
            //判断重复新增
            if(GameCategoryRelation::where($data)->count()){
                return false;
            }
        }
        $model->platform_id   = request('platform_id');
        $model->category_id   = request('category_id');
        $model->kind_id   = request('game_id');
        $model->sort      = request('sort', 0);
        $model->status    = request('status', 1);
        $res = $model->save();
        $game = OuterPlatformGame::where('kind_id',$model->kind_id)->where('platform_id',$model->platform_id)->first();
        if($res && $game){
            if(request()->has('icon')){
                $game->icon = request('icon','');
            }
            $game->save();
        }
        return $model->save();
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            //删除分类时把有关的热门分类也删除
            if((GameCategory::find($model->category_id)->name ?? '') != GameCategory::HOT_CATEGORY){
                self::where('platform_id',$model->platform_id)->where('kind_id',$model->kind_id)->delete();
            }
        });
        static::updating(function ($model) {
            $hotId = GameCategory::where('name',GameCategory::HOT_CATEGORY)->value('id');
            $noHotIds = GameCategory::where('name','<>',GameCategory::HOT_CATEGORY)->pluck('id');
            $status = self::where('platform_id',$model->platform_id)->where('kind_id',$model->kind_id)->whereIn('category_id',$noHotIds)->value('status');
            //其他分类状态禁用热门分类也禁用
            if($model->category_id == $hotId){
                if($model->status == 1 && $status == 2){
                    $model->status = 2;
                    $model->save();
                }
            }else{
                self::where('platform_id',$model->platform_id)->where('kind_id',$model->kind_id)->where('category_id',$hotId)->update(['status' => $model->status]);
            }
        });
    }
}

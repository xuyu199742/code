<?php

namespace Models\OuterPlatform;

class OuterPlatformGame extends Base
{
    protected $table = 'outer_platform_game';

    const STATUS_ON   = 1;
    const STATUS_OFF  = 2;
    const STATUS      = [
        self::STATUS_ON  => '启用',
        self::STATUS_OFF => '禁用',
    ];
    const SERVER_STATUS_ON   = 1;
    const SERVER_STATUS_OFF  = 2;
    const SERVER_STATUS      = [
        self::SERVER_STATUS_ON  => '正常',
        self::SERVER_STATUS_OFF => '维护',
    ];

    const HOT_SORTS  = [
        GameCategory::HOT_SORT => self::HOT_SORT,
        GameCategory::EHOT_SORT => self::EHOT_SORT,
        GameCategory::QHOT_SORT => self::QHOT_SORT,
    ];
    const HOT_SORT   = 'hot_sort';
    const EHOT_SORT  = 'ehot_sort';
    const QHOT_SORT  = 'qhot_sort';

    protected $fillable = ['platform_alias','kind_id','type','platform_id','name','server_status'];
    public $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        return $this->icon ? cdn($this->icon) : '';
    }

    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status] ?? '';
    }

    public function getServerStatusTextAttribute()
    {
        return self::STATUS[$this->server_status] ?? '';
    }

    public function getServerStatusAttribute()
    {
        $server_status = $this->platform->server_status == 1 ? 1 : 2;
        unset($this->platform);
        return $server_status;
    }

    //关联平台
    public function platform(){
        return $this->belongsTo(OuterPlatform::class,'platform_id','id');
    }

    /*批量操作*/
    public static function saveBulkAction($relation_ids)
    {
        if(!empty($relation_ids)){
            return self::whereIn('id',$relation_ids)->update(['status' => request('status',1)]);
        }
        return false;
    }

    //获取热门排序字段
    public static function getSortField($category_id){
        $category_id = GameCategory::where('id',$category_id)->where('tag',GameCategory::HOT_TAG)->value('id');
        return self::HOT_SORTS[$category_id] ?? '';
    }

    /*单条保存*/
    public static function saveOne($platform_id)
    {
        $model              = self::where('platform_alias',request('platform_alias'))->where('kind_id',request('kind_id'))->first();
        if (!$model) {
            $model                 = new self();
            $model->platform_alias = request('platform_alias', 0);
            $model->platform_id    = $platform_id;
            $model->kind_id        = request('kind_id', 0);
        }
        $model->icon          = request('icon', '');
        $model->icons         = request('icons', '');
        $model->img           = request('img', '');
        $model->name          = request('name', '');
        $model->description   = request('description', '');
        $model->sort          = request('sort', 0);
        $model->status        = request('status', 1);
        $model->server_status = request('server_status', 1);
        return $model->save();
    }

    /*热门分类下游戏编辑*/
    public static function EditHotOne()
    {
        $model = self::where('kind_id',request('kind_id'))->where('platform_id',request('platform_id'))->first();
        if(!$model){
            return false;
        }
        $hot_field = self::getSortField(request('category_id'));
        if(!$hot_field){
            return false;
        }
        $model->$hot_field  = request('sort', 0);
        $model->status    = request('status', 1);
        if(request()->has('icon')){
            $model->icon = request('icon','');
        }
        return $model->save();
    }

    /*热门分类下游戏编辑*/
    public static function AddHotMore($hot_field,$more)
    {
        \DB::beginTransaction([OuterPlatform::connectionName()]);
        foreach($more as $kind_id => $sort){
            $model = self::where('kind_id',$kind_id)->where('platform_id',request('platform_id'))->update([$hot_field => $sort]);
            if(!$model){
                \DB::rollBack([OuterPlatform::connectionName()]);
                return false;
            }
        }
        \DB::commit([OuterPlatform::connectionName()]);
        return true;
    }

    /*热门分类下游戏移除*/
    public static function DelHotOne()
    {
        $model = self::find(request('id'));
        if(!$model){
            return false;
        }
        $hot_field = self::getSortField(request('category_id'));
        if(!$hot_field){
            return false;
        }
        $model->$hot_field  = 0;
        return $model->save();
    }

    /*批量操作*/
    public static function saveBulkHotAction()
    {
        if(!empty(request('ids'))){
            return self::whereIn('id',request('ids'))->update(['status' => request('status',1)]);
        }
        return false;
    }

}

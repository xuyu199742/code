<?php

namespace Models\OuterPlatform;

class OuterPlatform extends Base
{
    protected $table = 'outer_platform';
    protected $fillable = ['alias','name'];
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

    const SELF_PLATFORM = 'douyou';

    public function getStatusTextAttribute()
    {
        return self::STATUS[$this->status]??'';
    }

    public function getServerStatusTextAttribute()
    {
        return self::STATUS[$this->server_status] ?? '';
    }

    /*关联游戏*/
    public function games()
    {
        return $this->hasMany(OuterPlatformGame::class, 'platform_id','id');
    }

    /*单条保存*/
    public static function saveOne($alias)
    {
        $model              = self::where('alias',$alias)->first();
        if (!$model) {
            $model          = new self();
            $model->alias   = $alias;
        }
        $model->icon        = request('icon','');
        $model->web_icon    = request('web_icon','');
        $model->icons       = request('icons','');
        $model->img         = request('img','');
        $model->name        = request('name', '');
        $model->description = request('description', '');
        $model->sort        = request('sort', 0);
        if(request()->has('status')) {
            $model->status = request('status', 1);
        }
        if(request()->has('server_status')){
            $model->server_status  = request('server_status', 1);
        }
        /*//位运算owned
        $owneds = request('category',[]);
        $owneds = array_column($owneds,'owned','id');
        ksort($owneds);
        $owned = 0;
        $i = 0;
        foreach ($owneds as $k => $v){
            if ($v == 1){
                $owned += pow(2,$i);
            }
            $i++;
        }
        $model->owned = $owned;*/
        return $model->save();
    }

    /*批量操作*/
    public static function saveBulkAction($platform_ids)
    {
        if(!empty($platform_ids)){
            return self::whereIn('id',$platform_ids)->update(['status' => request('status',1)]);
        }
        return false;
    }
}

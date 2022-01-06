<?php

namespace Models\AdminPlatform;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUser extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    use HasRoles;

    public    $connection = 'admin_platform';
    protected $guard_name = 'admin';

    const MALE   = 1;
    const FEMALE = 0;
    const SEX    = [
        self::FEMALE => '女',
        self::MALE   => '男'
    ];
    use Notifiable;

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::saving(function ($model) {
            if (!$model->modifySuper()) {
                throw new \ErrorException('无法修改超级管理员', 200);
            }
        });
        static::deleting(function ($model) {
            if (in_array($model->id, config('super.id'))) {
                throw new \ErrorException('无法禁用超级管理员', 200);
            }
        });
    }


    protected $fillable = [
        'username', 'email', 'password', 'mobile', 'sex'
    ];


    protected $hidden = [
        'password', 'remember_token',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'admin_id', 'id');
    }

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['role' => 'admin'];
    }

    public function getStatusTextAttribute()
    {
        return empty($this->deleted_at) ? '启用' : '禁用';
    }

    public function getSexTextAttribute()
    {
        return self::SEX[$this->sex] ?? '';
    }

    public function scopeMultiSearch($query, array $columns)
    {
        foreach ($columns as $column) {
            $value = request($column);
            if ($value) {
                $query->where($column, $value);
            }
        }
        return $query;
    }

    public function super()
    {
        if (in_array(Auth::guard('admin')->user()->id, config('super.id')) ) {
            return true;
        }
        return false;
    }

    public function modifySuper()
    {
        $login_id  = Auth::guard('admin')->user()->id;
        $super_ids = config('super.id');
        //dd($this->id,$login_id,$super_ids);
        if (in_array($this->id, $super_ids)) {
            if (in_array($login_id, $super_ids) && $login_id == $this->id) {
                return true;
            }
            return false;
        }
        return true;
    }


}

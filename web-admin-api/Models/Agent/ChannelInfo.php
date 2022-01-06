<?php
/* 渠道信息模型*/

namespace Models\Agent;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Models\AdminPlatform\PaymentOrder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Models\AdminPlatform\SystemLog;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ChannelInfo extends Authenticatable implements JWTSubject
{
    protected $connection = 'agent';
    use SoftDeletes;
    protected $table      = 'channel_info';
    protected $dates      = ['deleted_at'];
    protected $guarded    = [];
    protected $hidden     = ['password'];
    protected $primaryKey = 'channel_id';

    const FATHER = 'father_channel';
    const SON    = 'son_channel';

    const RECHARGE_TYPE = 1; //充值返利类型
    const STREAM_TYPE   = 2;  //流水返利类型

    const NULLITY_ON   = 0;
    const NULLITY_OFF  = 1;
    const NULLITY      = [
        self::NULLITY_ON  => '启用',
        self::NULLITY_OFF => '禁用',
    ];

    //定义禁止礼金勾选字段
    const FORBID_GIFTS = [
        'reg_give'  => 1,//注册赠送
        'bind_give' => 2,//绑定赠送
    ];

    // 渠道充值返利
    public static function RechargeRebate(PaymentOrder $order)
    {
        $channel_id = ChannelUserRelation::where('user_id', $order->user_id)->pluck('channel_id');
        if (!$channel_id) {
            return false;
        } else {
            $model = self::where('channel_id', $channel_id)->where('nullity',self::NULLITY_ON)->first();
            $money = ($order->coins) * ($model->return_rate * 100) / 10000;
            if ($model->return_type == self::RECHARGE_TYPE) {   //充值返利
                \DB::connection('agent')->beginTransaction();
                try {
                    //增加渠道金币数
                    $model->balance += $money;
                    //生成渠道收入记录
                    $data               = new ChannelIncome();  //渠道收入记录表
                    $data->channel_id   = $model->channel_id;  //关联的的渠道id
                    $data->record_type  = self::RECHARGE_TYPE; //返利类型：充值返利
                    $data->user_id      = $order->user_id;
                    $data->stream_score = (int)$order->coins;
                    $data->return_score = (int)$money;
                    $data->kind_id      = 0;
                    $data->server_id    = 0;
                    $data->created_at   = date('Y-m-d H:i:s');
                    if ($model->save() && $data->save()) {
                        \DB::connection('agent')->commit();
                    }
                } catch (\Exception $e) {
                    \DB::connection('agent')->rollback();
                    return false;
                }
                if ($model->parent_id == 0) {  //该渠道是一级渠道
                    return false;
                } else {        //该渠道是子渠道
                    $parent_channel_id = $model->parent_id; // 父渠道
                    $parent_model      = self::where('channel_id', $parent_channel_id)->where('nullity',self::NULLITY_ON)->first(); //获取父渠道的返利比例
                    $parent_money      = (($order->coins) * ($parent_model->return_rate) * 100 / 10000) - $money;
                    \DB::connection('agent')->beginTransaction();
                    try {
                        //增加渠道金币数
                        $parent_model->balance += $parent_money;
                        //生成渠道收入记录
                        $parent_data               = new ChannelIncome();  //渠道收入记录表
                        $parent_data->channel_id   = $parent_model->channel_id;  //关联的的渠道id
                        $parent_data->record_type  = self::RECHARGE_TYPE; //返利类型：充值返利
                        $parent_data->user_id      = $order->user_id;
                        $parent_data->stream_score = $order->coins;
                        $parent_data->return_score = $parent_money;
                        $parent_data->kind_id      = 0;
                        $parent_data->server_id    = 0;
                        $parent_data->created_at   = date('Y-m-d H:i:s');
                        if ($parent_model->save() && $parent_data->save()) {
                            \DB::connection('agent')->commit();
                        }
                    } catch (\Exception $e) {
                        \DB::connection('agent')->rollback();
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public function getRole(){
        if ($this->parent_id == 0) {
            return ChannelInfo::FATHER;
        }
        return ChannelInfo::SON;
    }


    public function scopeAndFilterWhere($query, ...$parameters)
    {
        $value = $this->getValue(...$parameters);
        if ($value) {
            return $query->where(...$parameters);
        }
        return $query;
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
        return ['role' => 'channel'];
    }

    /*public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        $datapassword = substr(md5($plain), 8, 16);

//        return md5($plain) == $user->getAuthPassword();
        return $datapassword == $user->getAuthPassword();
    }*/


    //父级方法
    public function scopeOrFilterWhere($query, ...$parameters)
    {
        $value = $this->getValue(...$parameters);
        if ($value) {
            return $query->orWhere(...$parameters);
        }
        return $query;
    }

    public function scopeMultiSearch($query, array $columns)
    {
        $query = collect($columns)->map(function ($column) use ($query) {
            $value = request($column);
            if ($value) {
                $query->where($column, $value);
            }
        });
        return $query;
    }

    public function scopeAndFilterBetweenWhere($query, $column, $start_time, $end_time)
    {
        if ($start_time) {
            $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
            $query->where($column, '>=', $start_time);
        }
        if ($end_time) {
            $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
            $query->where($column, '<=', $end_time);
        }
        return $query;
    }

    private function getValue(...$parameters)
    {
        if (count($parameters) == 3) {
            return $parameters[2];
        }
        return $parameters[1] ?? '';
    }

    public function scopeConnectionName()
    {
        return $this->connection;
    }

    public static function beginTransaction(array $connections)
    {
        foreach ($connections as $connection) {
            \DB::connection($connection)->beginTransaction();
        }
    }

    public static function rollBack(array $connections)
    {
        foreach ($connections as $connection) {
            \DB::connection($connection)->rollBack();
        }
    }

    public static function commit(array $connections)
    {
        foreach ($connections as $connection) {
            \DB::connection($connection)->commit();
        }
    }

    public function scopeLoadFromRequest()
    {
        $columns = Schema::connection($this->getConnectionName())->getColumnListing($this->getTable());
        $key     = $this->getKeyName();
        $guarded = $this->guarded;
        foreach (request()->all() as $attribute => $value) {
            if (in_array($attribute, $columns) && $attribute != $key && !in_array($attribute, $guarded)) {
                $this->setAttribute($attribute, $value);
            }
        }
        /*try {
            if ($this->save()) {
                return true;
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;*/
    }

    /*
     * 数组条件查询（值包含数字0）
     *
     */
    public function scopeMultiWhere($query, $arrColumn)
    {
        if (!empty($arrColumn) && is_array($arrColumn)) {
            foreach ($arrColumn as $key => $value) {
                if ($value || $value === 0) {
                    if (is_array($value)) {
                        $args = [$key, $value[0], $value[1]];
                        call_user_func_array([$query, 'where'], $args);
                    } else {
                        $args = [$key, $value];
                        call_user_func_array([$query, 'where'], $args);
                    }
                }
            }
        }
        return $query;
    }

    public  function scopeTableName()
    {
        return $this->getConnection()->getDatabaseName().'.dbo.'.$this->getTable();
    }
}

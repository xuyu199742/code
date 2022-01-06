<?php

namespace Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    public function scopeAndFilterWhere($query, ...$parameters)
    {
        $value = $this->getValue(...$parameters);
        if ($value) {
            return $query->where(...$parameters);
        }
        if ($value == 0 && $value != '') {
            return $query->where(...$parameters);
        }
        return $query;
    }


    public function scopeOrFilterWhere($query, ...$parameters)
    {
        $value = $this->getValue(...$parameters);
        if ($value) {
            return $query->orWhere(...$parameters);
        }
        if ($value == 0 && $value != '') {
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

    public  function scopeTable()
    {
        $table=$this->getConnection()->getDatabaseName().'.dbo.'.$this->table;
        return $this->setTable($table);
    }
    public  function scopeTableName()
    {
        return $this->getConnection()->getDatabaseName().'.dbo.'.$this->getTable();
    }

    public function scopeAndFilterIntervalWhere($query, $column, $start, $end)
    {
        if ($start) {
            $query->where($column, '>=', $start);
        }
        if ($end) {
            $query->where($column, '<=', $end);
        }
        return $query;
    }

    public function scopeAndFilterIntervalHaving($query, $column,$start, $end)
    {
        if (is_numeric($start)) {
            $query->having($column, '>=', $start);
        }
        if (is_numeric($end)) {
            $query->having($column, '<=', $end);
        }
        return $query;
    }
}

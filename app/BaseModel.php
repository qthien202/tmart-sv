<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();
        // Write Log
        static::creating(function ($model) {
            // $user_id = SERVICE::getCurrentUserId();
            // $user = !empty($user_id) ? $user_id : 0;
            // $model->created_by = $user;
            // $model->updated_by = $user;
            $date = date('Y-m-d H:i:s', time());
            $model->created_at = $date;
            $model->updated_at = $date;
        });

        static::updating(function ($model) {
            // $user_id = SERVICE::getCurrentUserId();
            // $user = !empty($user_id) ? $user_id : 0;
            // $model->updated_by = $user;
        });

        static::saving(function ($model) {
            // $user_id = SERVICE::getCurrentUserId();
            // $user = !empty($user_id) ? $user_id : 0;
            // $model->updated_by = $user;
            $model->updated_at = date('Y-m-d H:i:s', time());
        });

        static::deleting(function ($model) {
            // $user_id = SERVICE::getCurrentUserId();
            // $user = !empty($user_id) ? $user_id : 0;
            // $model->deleted = 1;
            // $model->deleted_by = $user;
            $model->save();
        });
    }

    /**
     * @return array
     */
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * @param $query
     * @param int $is_active
     */
    public function filterData(&$query, $is_active = 1)
    {
        $query->where($this->getTable() . '.is_active', $is_active);
    }

    public static final function model()
    {
        $classStr = get_called_class();
        /** @var Model $class */
        $class = new $classStr();
        return $class::whereNull($class->getTable() . '.deleted_at');
    }
}

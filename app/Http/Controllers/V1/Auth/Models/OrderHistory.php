<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderHistory extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_histories';

    protected $fillable = [
        'id',
        'user_id',
        'order_id',
        'status_code',
        'note',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['order_id'])) {
            $query->where('order_id', $params['order_id']);
        }
        if (isset($params['status_code'])) {
            $query->where('status_code', $params['status_code']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }


}
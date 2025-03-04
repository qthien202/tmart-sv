<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderStatus extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_status';

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'default',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['code'])) {
            $query->where('code', $params['code']);
        }
        if (isset($params['description'])) {
            $query->where('description', $params['description']);
        }
        if (isset($params['name'])) {
            $query->where('name', $params['name']);
        }
        if (isset($params['default'])) {
            $query->where('default', $params['default']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
}
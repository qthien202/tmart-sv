<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Favorite extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'favorites';

    protected $fillable = [
        'id',
        'user_id',
        'product_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // protected $casts = [];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (isset($params['product_id'])) {
            $query->where('order_number', $params['order_number']);
        }
        $query->orderByDesc('created_at');
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function product(){
        return $this->hasOne(Product::class,"id","product_id");
    }
}
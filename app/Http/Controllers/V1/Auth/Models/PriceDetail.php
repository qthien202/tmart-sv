<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class PriceDetail extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'price_details';

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "product_id",
        "price_id",
        "price",
        "currency",
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['product_id'])) {
            $query->where('product_id', $params['product_id']);
        }
        if (isset($params['price_id'])) {
            $query->where('price_id', $params['price_id']);
        }
        if (isset($params['price'])) {
            $query->where('price', $params['price']);
        }
        if (isset($params['currency'])) {
            $query->where('currency', $params['currency']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
    public function prices(){
        return $this->belongsTo(Price::class, "price_id");
    }
}
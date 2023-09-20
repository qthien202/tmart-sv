<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderShipping extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    
    protected $table = 'order_shippings';

    protected $fillable = [
        'id',
        'order_id',
        'status',
        'shipping_date',
        'estimated_delivery_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function orderShippingDetail(){
        return $this->hasOne(OrderShippingDetail::class,"order_shipping_id","id");
    }

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['order_id'])) {
            $query->where('order_id', $params['order_id']);
        }
        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }
        if (isset($params['shipping_date'])) {
            $query->where('shipping_date', $params['shipping_date']);
        }
        if (isset($params['estimated_delivery_date'])) {
            $query->where('estimated_delivery_date', $params['estimated_delivery_date']);
        }
        
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
}
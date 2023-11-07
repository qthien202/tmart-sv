<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Order extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'user_id',
        'order_number',
        'info_total_amount',
        'status_code',
        'shipping_company_id',
        'name',
        'phone',
        'payment_uid',
        'coupon_code',
        'voucher_code',
        'note',
        'recipient_address',
        'shipping_address',
        'billing_address',
        'free_item',
        'order_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'info_total_amount' => 'json',
        'free_item' => 'json',
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (isset($params['order_number'])) {
            $query->where('order_number', $params['order_number']);
        }
        if (isset($params['name'])) {
            $query->where('name', $params['name']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
        // return $this->hasOne(OrderDetail::class,'order_id','id');
    }
    public function orderHistories(){
        return $this->hasOne(OrderHistory::class);
        // return $this->hasOne(OrderDetail::class,'order_id','id');
    }
}
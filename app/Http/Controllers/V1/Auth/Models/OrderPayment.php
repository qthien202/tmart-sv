<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderPayment extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_payments';

    protected $fillable = [
        'id',
        'order_id',
        'payment_method',
        'payment_status',
        'amount',
        'paydate',
        'bank_code',
        'bank_tran_no',
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
        if (isset($params['payment_method'])) {
            $query->where('payment_method', $params['payment_method']);
        }
        if (isset($params['payment_status'])) {
            $query->where('payment_status', $params['payment_status']);
        }
        if (isset($params['amount'])) {
            $query->where('amount', $params['amount']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

}
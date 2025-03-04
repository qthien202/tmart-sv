<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderPromotion extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_promotions';

    protected $fillable = [
        'id',
        'order_id',
        'name',
        'discount_amount',
        'description',
        'start_date',
        'end_date',
        'promotion_type',
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
        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (isset($params['discount_amount'])) {
            $query->where('discount_amount', $params['discount_amount']);
        }
        if (isset($params['description'])) {
            $query->where('description', $params['description']);
        }
        // Date
        if (isset($params['start_date'])) {
            $query->where('start_date', $params['start_date']);
        }
        if (isset($params['end_date'])) {
            $query->where('end_date', $params['end_date']);
        }
        if (isset($params['promotion_type'])) {
            $query->where('promotion_type', $params['promotion_type']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
}
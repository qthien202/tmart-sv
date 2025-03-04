<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class ShippingCompany extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'shipping_companies';

    protected $fillable = [
        'id',
        'shipping_company',
        'contact',
        'estimated_shipping_time',
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['shipping_company'])) {
            $query->where('shipping_company', $params['shipping_company']);
        }
        if (isset($params['contact'])) {
            $query->where('contact', $params['contact']);
        }
        if (isset($params['estimated_shipping_time'])) {
            $query->where('estimated_shipping_time', $params['estimated_shipping_time']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
}
<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderShippingDetail extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_shipping_details';

    protected $fillable = [
        'id',
        'shipping_id',
        'shipping_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
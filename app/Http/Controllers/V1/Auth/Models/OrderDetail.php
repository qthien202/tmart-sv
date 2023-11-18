<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class OrderDetail extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'order_details';

    protected $fillable = [
        'id',
        'order_id',
        'product_id',
        'quantity',
        'price',
        'option',
        'subtotal',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $casts =[
        "option"=>"json"
    ];

    public function product(){
        return $this->hasOne(Product::class,"id","product_id");
    }

}
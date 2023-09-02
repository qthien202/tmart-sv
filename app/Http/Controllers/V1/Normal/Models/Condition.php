<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Condition extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'conditions';

    protected $primaryKey = "id";

    protected $fillable = [
        "id",
        "coupon_code",
        "voucher_code",
        "max_discoun",
        "min_order_amount",
        "first_order_only",
        "mobile_app_only",
        "for_loged_in_users",
        "applicable_product"
    ];

    // public function setCondition($request){
    //     // $request->validate([
    //     //     'coupon_code' => 'numeric'
    //     // ]);
    //     return self::create($request->all());
    // }
}
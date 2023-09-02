<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'coupons';

    protected $primaryKey = "coupon_code";

    protected $fillable = [
        "coupon_code",
        "coupon_value",
        "coupon_type",
        "title",
        "coupon_date_start",
        "coupon_date_end"
    ];

    public function conditions() {
        return $this->hasOne(Condition::class,"coupon_code","coupon_code");
    }
}
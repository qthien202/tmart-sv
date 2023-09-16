<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'vouchers';

    // protected $primaryKey = "voucher_code";

    protected $fillable = [
        "voucher_code",
        "voucher_value",
        "voucher_type",
        "title",
        "voucher_date_start",
        "voucher_date_end"
    ];

    public function conditions() {
        return $this->hasOne(Condition::class,"voucher_code","voucher_code");
    }
}
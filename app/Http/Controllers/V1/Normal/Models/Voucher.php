<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Voucher extends Model
{
    // public $timestamps = false;
    // use SoftDeletes;

    protected $table = 'vouchers';

    public $incrementing = false;

    protected $primaryKey = "voucher_code";

    protected $fillable = [
        "voucher_code",
        "voucher_value",
        "voucher_type",
        "title",
        "is_active",
        "voucher_date_start",
        "voucher_date_end"
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['voucher_code'])) {
            $query->where('voucher_code', $params['voucher_code']);
        }
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }
        if (isset($params['voucher_value'])) {
            $query->where('voucher_value', $params['voucher_value']);
        }
        if (isset($params['voucher_type'])) {
            $query->where('voucher_type', $params['voucher_type']);
        }
        if (isset($params['title'])) {
            $query->where('title', $params['title']);
        }
        $query->orderByDesc('created_at');
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function condition() {
        return $this->hasOne(Condition::class,"voucher_code","voucher_code");
    }
}
<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Manufacturer extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'manufacturers';

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "name",
        "address",
        "phone",
        "email",
        "website",
        "field",
        "year_established",
        "product_infomation"
    ];

    public function scopeSearch($query, $params){
        $query->select('id', 'name', 'address','phone','email','website','field','year_established','product_infomation','created_at','deleted_at');
        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (isset($params['address'])) {
            $query->where('address', $params['address']);
        }
        if (isset($params['phone'])) {
            $query->where('phone', $params['phone']);
        }
        if (isset($params['email'])) {
            $query->where('email', $params['email']);
        }
        if (isset($params['website'])) {
            $query->where('website', $params['website']);
        }
        if (isset($params['field'])) {
            $query->where('field', $params['field']);
        }
        if (isset($params['year_established'])) {
            $query->where('year_established', $params['year_established']);
        }
        if (isset($params['product_infomation'])) {
            $query->where('product_infomation', $params['product_infomation']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }
    public function products(){
        return $this->hasMany(Product::class,'manufacturer_id','id');
    }
}
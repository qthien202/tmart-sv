<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class PackagingUnit extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'packaging_units';

    protected $primaryKey = "id";

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "product_id",
        "base_unit_quantity",
        "packaging_unit_name",
        "packaging_quantity",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function scopeSearch($query, $params){
        $query->select('id', 'product_id', 'base_unit_quantity', 'packaging_unit_name','packaging_quantity', 'created_at','updated_at');
        if (isset($params['product_id'])) {
            $query->where('product_id', $params['product_id']);
        }
        if (isset($params['base_unit_quantity'])) {
            $query->where('base_unit_quantity', $params['base_unit_quantity']);
        }
        if (isset($params['packaging_unit_name'])) {
            $query->where('packaging_unit_name', $params['packaging_unit_name']);
        }
        if (isset($params['packaging_quantity'])) {
            $query->where('packaging_quantity', $params['packaging_quantity']);
        }
        
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

}
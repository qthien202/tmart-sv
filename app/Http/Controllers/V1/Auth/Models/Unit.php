<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Unit extends Model
{
    public $timestamps = false;
    // use SoftDeletes;

    protected $table = 'units';

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "code",
        "name"
    ];

    public function scopeSearch($query, $params){
        $query->select('id', 'code', 'name');
        if (isset($params['code'])) {
            $query->where('code', $params['code']);
        }
        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }


}
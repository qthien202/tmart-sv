<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class AddressBook extends Model
{
    use SoftDeletes;

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = 'address_books';

    protected $fillable = [
        'id',
        'user_id',
        'full_name',
        'phone',
        'ward_id',
        'ward_name', 
        'district_id', 
        'district_name', 
        'city_id', 
        'city_name', 
        'full_address', 
        'created_at', 
        'update_at', 
        'deleted_at', 
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    // public 
}
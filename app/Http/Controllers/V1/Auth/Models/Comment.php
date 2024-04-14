<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Comment extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'comments';

    protected $primaryKey = "id";

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "product_id",
        "user_id",
        "text",
        "image_url",
        "rating",
        "parent_id",
    ];

    protected $casts = [
        'image_url' => 'json'
    ];

    public function scopeSearch($query, $params){
        $query->select('*');

        if (isset($params['product_id'])) {
            $query->where('product_id', $params['product_id']);
        }
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (isset($params['parent_id'])) {
            $query->where('parent_id', $params['parent_id']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function comment(){
        return $this->hasOne(Comment::class,"parent_id","id");
    }

}
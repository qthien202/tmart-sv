<?php

namespace App\Http\Controllers\V1\Auth\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Notification extends Model{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $primaryKey = "id";
    
    protected $fillable = [
        "id",
        "user_id",
        "order_id",
        "title",
        "content",
        "image_url"
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['order_id'])) {
            $query->where('order_id', $params['order_id']);
        }
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (isset($params['title'])) {
            $query->where('title', $params['title']);
        }
        $query->orderByDesc('created_at');
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function user(){
        $this->belongsTo(User::class,"user_id","user");
    }
}
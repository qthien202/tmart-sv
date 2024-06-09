<?php

namespace App\Http\Controllers\V1\Auth\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function user(){
        $this->belongsTo(User::class,"user_id","user");
    }
}
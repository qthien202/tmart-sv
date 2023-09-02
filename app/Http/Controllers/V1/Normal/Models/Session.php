<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    // public $timestamps = false;

    protected $table = 'sessions';

    protected $primaryKey = "id";

    protected $fillable = [
        "id",
        "session_id"
    ];
    public function cart(){
        return $this->hasOne(Cart::class,"guest_id","id");
    }

}
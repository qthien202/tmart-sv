<?php

namespace App;

use App\Http\Controllers\V1\Auth\Models\Notification;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden
        = [
            'password',
        ];

    protected $fillable
        = [
            'username',
            'phone',
            'code',
            'password',
            'email',
            'first_name',
            'last_name',
            'short_name',
            'full_name',
            'address',
            'birthday',
            'avatar',
            'genre',
            'id_number',
            'verify_code',
            'expired_code',
            'role_id',
            'note',
            'price_show',
            'is_active',
            'deleted',
            'created_at',
            'created_by',
            'updated_by',
            'updated_at',
        ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function notifications(){
        return $this->hasMany(Notification::class,"user_id","id");
    }
}

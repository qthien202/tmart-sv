<?php

namespace App\Http\Controllers\V1\Auth\Resources\User;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;
use Exception;

class UserResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $birthday = object_get($this, 'birthday', null);
            return [
                'id'              => $this->id,
                'code'            => $this->code,
                'phone'           => $this->phone,
                'email'           => $this->email,
                'username'        => $this->username,
                'first_name'      => object_get($this, "first_name", null),
                'last_name'       => object_get($this, "last_name", null),
                'full_name'       => object_get($this, "full_name", null),
                'address'         => object_get($this, "address", null),
                'birthday'        => !empty($birthday) ? date('d/m/Y', strtotime($birthday)) : null,
                'genre'           => object_get($this, "genre", "O"),
                'genre_name'      => config('constants.STATUS.GENRE')[strtoupper(object_get($this, "genre", 'O'))],
                'avatar'          => object_get($this, "avatar", null),
                'id_number'       => object_get($this, "id_number", null),
                'is_active'       => $this->is_active,
                'role_id'         => $this->role_id,
                'created_at'      => date('d/m/Y H:i', strtotime($this->created_at)),
                'updated_at'      => date('d/m/Y H:i', strtotime($this->updated_at)),
                'created_by'      => object_get($this, "createdBy.full_name", null),
                'updated_by'      => object_get($this, "updatedBy.full_name", null),
            ];
        } catch (Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new Exception($response['message']);
        }
    }
}

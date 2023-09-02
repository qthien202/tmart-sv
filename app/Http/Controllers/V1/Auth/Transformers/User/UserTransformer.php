<?php

namespace  App\Http\Controllers\V1\Auth\Transformers\User;

use App\Supports\SERVICE_Error;
use App\User;
use Exception;
use League\Fractal\TransformerAbstract;

/**
 * Class UserTransformer
 *
 * @package App\V1\CMS\Transformers
 */
class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        try {
            $birthday = object_get($user, 'birthday', null);
            return [
                'id'              => $user->id,
                'code'            => $user->code,
                'phone'           => $user->phone,
                'email'           => $user->email,
                'username'        => $user->username,
                'first_name'      => object_get($user, "first_name", null),
                'last_name'       => object_get($user, "last_name", null),
                'full_name'       => object_get($user, "full_name", null),
                'address'         => object_get($user, "address", null),
                'birthday'        => !empty($birthday) ? date('d/m/Y', strtotime($birthday)) : null,
                'genre'           => object_get($user, "genre", "O"),
                'genre_name'      => config('constants.STATUS.GENRE')[strtoupper(object_get($user, "genre", 'O'))],
                'avatar'          => object_get($user, "avatar", null),
                'id_number'       => object_get($user, "id_number", null),
                'is_active'       => $user->is_active,
                'role_id'         => $user->role_id,
                'created_at'      => date('d/m/Y H:i', strtotime($user->created_at)),
                'updated_at'      => date('d/m/Y H:i', strtotime($user->updated_at)),
                'created_by'      => object_get($user, "createdBy.full_name", null),
                'updated_by'      => object_get($user, "updatedBy.full_name", null),
            ];
        } catch (Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new Exception($response['message'], $response['code']);
        }
    }
}

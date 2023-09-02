<?php

namespace  App\Http\Controllers\V1\Auth\Transformers\User;

use App\Supports\SERVICE_Error;
use App\User;
use League\Fractal\TransformerAbstract;

class UserCustomerProfileTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        try {
            $birthday = object_get($user, 'profile.birthday', null);
            $profile[] = [
                'id'          => object_get($user, "id", null),
                'code'        => object_get($user, "code", null),
                'phone'       => object_get($user, "profile.phone", null),
                'first_name'  => object_get($user, "profile.first_name", null),
                'last_name'   => object_get($user, "last_name", null),
                'short_name'  => object_get($user, "short_name", null),
                'full_name'   => object_get($user, "profile.full_name", null),
                'address'     => object_get($user, "profile.address", null),
                'branch_name' => object_get($user, "profile.branch_name", null),
                'birthday'    => !empty($birthday) ? date('Y-m-d', strtotime($birthday)) : null,
                'genre'       => object_get($user, "profile.genre", "O"),
                'id_number'   => object_get($user, "profile.id_number", null),
                'is_active'   => object_get($user, "profile.is_active", null),
            ];
            $avatar = !empty($user->profile->avatar) ? url('/v0') . "/img/" . $user->profile->avatar : null;
            return [
                'id'          => $user->id,
                'code'        => $user->code,
                'phone'       => object_get($user, "profile.phone", null),
                'email'       => object_get($user, "profile.email", null),
                'type'        => $user->type,
                'user_type'   => $user->user_type,
                'first_name'  => object_get($user, "profile.first_name", null),
                'last_name'   => object_get($user, "profile.last_name", null),
                'short_name'  => object_get($user, "profile.short_name", null),
                'full_name'   => object_get($user, "profile.full_name", null),
                'address'     => object_get($user, "profile.address", null),
                'branch_name' => object_get($user, "profile.branch_name", null),
                'birthday'    => !empty($birthday) ? date('Y-m-d', strtotime($birthday)) : null,
                'genre'       => object_get($user, "profile.genre", "O"),
                'genre_name'  => config('constants.STATUS.GENRE')
                [strtoupper(object_get($user, "profile.genre", 'O'))],
                'avatar'      => $avatar,
                'id_number'   => object_get($user, "profile.id_number", null),
                'is_active'   => $user->is_active,
                'profile'     => $profile,
                'created_at'  => date('d/m/Y H:i', strtotime($user->created_at)),
                'updated_at'  => date('d/m/Y H:i', strtotime($user->updated_at)),
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message'], $response['code']);
        }
    }

}
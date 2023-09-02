<?php

namespace  App\Http\Controllers\V1\Auth\Transformers\User;


use App\User;
use League\Fractal\TransformerAbstract;

class UserProfileByCodeTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $avatar = !empty($user->profile->avatar) ? url('/v0') . "/img/" . $user->profile->avatar : null;
        try {
            return [
                'id' => $user->id,
                'code' => $user->code,
                'phone' => $user->phone,
                'email' => $user->email,
                'type' => $user->type,
                'first_name' => object_get($user, "profile.first_name", null),
                'last_name' => object_get($user, "profile.last_name", null),
                'short_name' => object_get($user, "profile.short_name", null),
                'full_name' => object_get($user, "profile.full_name", null),
                'address' => object_get($user, "profile.address", null),
                'avatar' => $avatar,
            ];
        } catch (\Exception $ex) {
            $response = SSC_Error::handle($ex);
            throw new \Exception($response['message'], $response['code']);
        }
    }
}

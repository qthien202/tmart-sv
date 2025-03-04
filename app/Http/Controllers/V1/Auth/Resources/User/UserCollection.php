<?php

namespace App\Http\Controllers\V1\Auth\Resources\User;

use App\Http\Resources\BaseCollection;

class UserCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}

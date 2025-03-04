<?php

namespace App\Http\Controllers\V1\Auth\Resources\Favorite;

use App\Http\Resources\BaseCollection;

class FavoriteCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
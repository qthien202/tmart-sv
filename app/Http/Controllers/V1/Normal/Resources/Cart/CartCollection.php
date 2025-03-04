<?php

namespace App\Http\Controllers\V1\Normal\Resources\Cart;

use App\Http\Resources\BaseCollection;

class CartCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
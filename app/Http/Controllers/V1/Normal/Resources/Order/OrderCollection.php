<?php

namespace App\Http\Controllers\V1\Auth\Resources\Order;

use App\Http\Resources\BaseCollection;

class OrderCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderStatus;

use App\Http\Resources\BaseCollection;

class OrderStatusCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
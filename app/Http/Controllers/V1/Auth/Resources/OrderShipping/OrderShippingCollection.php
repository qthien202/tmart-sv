<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderShipping;

use App\Http\Resources\BaseCollection;

class OrderShippingCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
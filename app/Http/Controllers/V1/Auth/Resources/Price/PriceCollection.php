<?php

namespace App\Http\Controllers\V1\Auth\Resources\Price;

use App\Http\Resources\BaseCollection;

class PriceCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
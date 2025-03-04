<?php

namespace App\Http\Controllers\V1\Auth\Resources\PriceDetail;

use App\Http\Resources\BaseCollection;

class PriceDetailCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
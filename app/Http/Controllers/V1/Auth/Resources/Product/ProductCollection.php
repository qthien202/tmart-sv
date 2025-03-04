<?php

namespace App\Http\Controllers\V1\Auth\Resources\Product;

use App\Http\Resources\BaseCollection;

class ProductCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
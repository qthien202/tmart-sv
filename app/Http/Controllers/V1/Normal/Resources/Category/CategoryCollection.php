<?php

namespace App\Http\Controllers\V1\Normal\Resources\Category;

use App\Http\Resources\BaseCollection;

class CategoryCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
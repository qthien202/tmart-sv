<?php

namespace App\Http\Controllers\V1\Auth\Resources\Category;

use App\Http\Resources\BaseCollection;

class CategoryCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
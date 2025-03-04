<?php

namespace App\Http\Controllers\V1\Auth\Resources\Unit;

use App\Http\Resources\BaseCollection;

class UnitCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
<?php

namespace App\Http\Controllers\V1\Auth\Resources\PackagingUnit;

use App\Http\Resources\BaseCollection;

class PackagingUnitCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
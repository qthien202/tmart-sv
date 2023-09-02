<?php

namespace App\Http\Controllers\V1\Auth\Resources\Manufacturer;

use App\Http\Resources\BaseCollection;

class ManufacturerCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
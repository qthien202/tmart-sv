<?php

namespace App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict;

use App\Http\Resources\BaseCollection;

class VietNamListDistrictWardCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
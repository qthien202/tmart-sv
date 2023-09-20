<?php

namespace App\Http\Controllers\V1\Auth\Resources\ShippingCompany;

use App\Http\Resources\BaseCollection;

class ShippingCompanyCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
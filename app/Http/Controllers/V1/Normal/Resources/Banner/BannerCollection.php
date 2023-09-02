<?php

namespace App\Http\Controllers\V1\Normal\Resources\Banner;

use App\Http\Resources\BaseCollection;

class BannerCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
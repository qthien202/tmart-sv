<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderPromotion;

use App\Http\Resources\BaseCollection;

class OrderPromotionCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
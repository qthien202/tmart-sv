<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderHistory;

use App\Http\Resources\BaseCollection;

class OrderHistoryCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
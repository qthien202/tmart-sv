<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderPayment;

use App\Http\Resources\BaseCollection;

class OrderPaymentCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
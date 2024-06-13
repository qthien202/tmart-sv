<?php

namespace App\Http\Controllers\V1\Auth\Resources\Voucher;

use App\Http\Resources\BaseCollection;

class VoucherCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
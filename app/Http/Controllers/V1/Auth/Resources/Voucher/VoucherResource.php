<?php

namespace App\Http\Controllers\V1\Auth\Resources\Voucher;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class VoucherResource extends BaseResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request)
    {
        try {
            $condition = $this->condition;
            return [
                'voucher_code'   => $this->voucher_code,
                'voucher_value' => $this->voucher_value,
                'voucher_type' => $this->voucher_type,
                'title' => $this->title,
                'voucher_date_start' => $this->voucher_date_start,
                'voucher_date_end' => $this->voucher_date_end,
                'max_discoun' => $condition->max_discoun,
                'min_order_amount' => $condition->min_order_amount,
                'first_order_only' => $condition->first_order_only,
                'mobile_app_only' => $condition->mobile_app_only,
                'for_loged_in_users' => $condition->for_loged_in_users,
                'applicable_product' => $condition->applicable_product,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

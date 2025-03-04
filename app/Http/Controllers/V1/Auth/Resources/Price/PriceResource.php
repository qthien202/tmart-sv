<?php

namespace App\Http\Controllers\V1\Auth\Resources\Price;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class PriceResource extends BaseResource
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
            return [
                'price_id'   => $this->id,
                "product_id" => $this->priceDetails()->value("product_id"),
                "price" => $this->priceDetails()->value("price"),
                "currency" => $this->priceDetails()->value("currency"),
                'effective_date' => $this->effective_date,
                'expire_date' => $this->expire_date,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

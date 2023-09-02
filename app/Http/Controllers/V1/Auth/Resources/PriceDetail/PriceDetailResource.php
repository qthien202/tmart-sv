<?php

namespace App\Http\Controllers\V1\Auth\Resources\PriceDetail;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class PriceDetailResource extends BaseResource
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
                'id'   => $this->id,
                'product_id' => $this->product_id,
                'price_id' => $this->price,
                'price' => $this->price,
                'currency' => $this->currency,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

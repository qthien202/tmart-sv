<?php

namespace App\Http\Controllers\V1\Auth\Resources\ShippingCompany;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class ShippingCompanyResource extends BaseResource
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
                'id' => $this->id,
                'shipping_company' => $this->shipping_company,
                'contact' => $this->contact,
                'estimated_shiping_time' => $this->estimated_shiping_time,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderShipping;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class OrderShippingResource extends BaseResource
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
                'order_id' => $this->order_id,
                'order_shipping_description' => $this->orderShippingDetail->shipping_description,
                'status_code' => $this->status_Code,
                'status'=> $this->status,
                'shipping_date'=> $this->shipping_date,
                'estimated_delivery_date'=> $this->estimated_delivery_date,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $this->deleted_at,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

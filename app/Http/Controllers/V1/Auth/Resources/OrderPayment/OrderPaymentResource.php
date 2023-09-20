<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderPayment;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class OrderPaymentResource extends BaseResource
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
                'payment_method' => $this->payment_method,
                'payment_status'=> $this->payment_status,
                'amount'=> $this->amount,
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

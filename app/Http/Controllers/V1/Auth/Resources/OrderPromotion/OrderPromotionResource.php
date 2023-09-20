<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderPromotion;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class OrderPromotionResource extends BaseResource
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
                'name' => $this->name,
                'discount_amount' => $this->discount_amount,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'promotion_type' => $this->promotion_type,
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

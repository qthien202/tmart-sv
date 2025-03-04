<?php

namespace App\Http\Controllers\V1\Auth\Resources\PackagingUnit;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class PackagingUnitResource extends BaseResource
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
                'base_unit_quantity' => $this->base_unit_quantity,
                'packaging_unit_name' => $this->packaging_unit_name,
                'packaging_quantity' => $this->packaging_quantity
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

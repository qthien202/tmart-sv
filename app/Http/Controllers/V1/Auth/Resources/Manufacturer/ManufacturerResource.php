<?php

namespace App\Http\Controllers\V1\Auth\Resources\Manufacturer;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class ManufacturerResource extends BaseResource
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
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'email' => $this->email,
                'website' => $this->website,
                'field' => $this->field,
                'year_established' => $this->year_established,
                'product_infomation' => $this->product_infomation,
                'created_at' => !empty($this->created_at) ? date('Y-m-d H:i:s', strtotime($this->created_at)) : null,
                'updated_at' => !empty($this->updated_at) ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : null,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

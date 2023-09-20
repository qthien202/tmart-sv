<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderStatus;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class OrderStatusResource extends BaseResource
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
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description,
                'default' => $this->default,
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

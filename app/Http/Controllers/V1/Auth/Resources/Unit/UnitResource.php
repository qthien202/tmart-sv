<?php

namespace App\Http\Controllers\V1\Auth\Resources\Unit;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class UnitResource extends BaseResource
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
                'code' => $this->code,
                'name' => $this->name,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

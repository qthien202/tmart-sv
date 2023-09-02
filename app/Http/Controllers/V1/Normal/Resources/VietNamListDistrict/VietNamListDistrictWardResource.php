<?php

namespace App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class VietNamListDistrictWardResource extends BaseResource
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
                'ward_code'      => $this->ward_code,
                'ward_type'      => $this->ward_type,
                'ward_name'      => $this->ward_name,
                'ward_full_name' => $this->ward_full_name,
                'level'          => $this->level,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}
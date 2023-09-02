<?php

namespace App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class VietNamListDistrictDistrictResource extends BaseResource
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
                'district_code'      => $this->district_code,
                'district_type'      => $this->district_type,
                'district_name'      => $this->district_name,
                'district_full_name' => $this->district_full_name,
                'wards'              => $this->view_ward == 1 ? $this->getWard->map(function ($ward) {
                    return [
                        'ward_code'      => $ward->ward_code,
                        'ward_type'      => $ward->ward_type,
                        'ward_name'      => $ward->ward_name,
                        'ward_full_name' => $ward->ward_full_name,
                        'level'          => $ward->level,
                    ];
                })->unique('ward_code')->values() : null
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}
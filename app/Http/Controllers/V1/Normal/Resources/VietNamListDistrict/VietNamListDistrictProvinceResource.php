<?php

namespace App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class VietNamListDistrictProvinceResource extends BaseResource
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
                'city_code'      => $this->city_code,
                'city_type'      => $this->city_type,
                'city_name'      => $this->city_name,
                'city_full_name' => $this->city_full_name,
                'districts'      => $request->view_district == 1 ? $this->getDistrict->map(function ($district) use ($request) {
                    return [
                        'district_code'      => $district->district_code,
                        'district_type'      => $district->district_type,
                        'district_name'      => $district->district_name,
                        'district_full_name' => $district->district_full_name,
                        'wards'              => $request->view_ward == 1 ? $district->getWard->map(function ($ward) {
                            return [
                                'ward_code'      => $ward->ward_code,
                                'ward_type'      => $ward->ward_type,
                                'ward_name'      => $ward->ward_name,
                                'ward_full_name' => $ward->ward_full_name,
                                'level'          => $ward->level,
                            ];
                        })->unique('ward_code')->values() : null
                    ];
                })->unique('district_code')->values() : null
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}
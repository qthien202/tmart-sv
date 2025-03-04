<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict\VietNamListDistrictDistrictCollection;
use App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict\VietNamListDistrictProvinceCollection;
use App\Http\Controllers\V1\Normal\Resources\VietNamListDistrict\VietNamListDistrictWardCollection;
use App\Http\Controllers\V1\Normal\Transformers\VietNamListDistrict\WardTransformer;
use App\VietNamListDistrict;
use Illuminate\Http\Request;

class VietNamListDistrictController extends BaseController
{
    /**
     * @return mixed
     */
    public function getJson(Request $request)
    {
        $file = base_path('dvhc/data.json');
        $json = json_decode(file_get_contents($file), true);
        if ($request->get('is_download', 0) == 1) {
            $fileName = date("Y_m_d_H_i_s", time()) . "_Đơn_vị_hành_chính_thuongtamduy_com.json";
            header('Content-Type: application/json');
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length: " . filesize($file));
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            readfile("$file");
        }
        return $json;
    }

    public function getXls()
    {
        $file = base_path('dvhc/data.xls');
        $fileName = date("Y_m_d_H_i_s", time()) . "_Đơn_vị_hành_chính_thuongtamduy_com.xls";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        readfile("$file");
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getProvince(Request $request)
    {
        $result = VietNamListDistrict::searchProvince($request)
            ->with(['getDistrict', 'getWard'])
            ->paginate($request->get('limit', 20));
        return new VietNamListDistrictProvinceCollection($result);
    }

    /**
     * @param $provinceCode
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getDistrict($provinceCode, Request $request)
    {
        $result = VietNamListDistrict::searchDistrict($request)
            ->where('city_code', $provinceCode)
            ->with(['getWard'])
            ->paginate($request->get('limit', 20));
        return new VietNamListDistrictDistrictCollection($result);
    }

    /**
     * @param $districtCode
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getWard($districtCode, Request $request)
    {
        $result = VietNamListDistrict::searchWard($request)
            ->where('district_code', $districtCode)
            ->paginate($request->get('limit', 20));
        return new VietNamListDistrictWardCollection($result);
    }
}

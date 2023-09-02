<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\PackagingUnit;
use App\Http\Controllers\V1\Auth\Resources\PackagingUnit\PackagingUnitCollection;
use App\Http\Controllers\V1\Auth\Resources\PackagingUnit\PackagingUnitResource;
use Illuminate\Http\Request;

class PackagingUnitController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new PackagingUnit();
    }

    public function getPackagingUnits(Request $request)
    {
        $packagingUnit = $this->model->search($request->all());
        return new PackagingUnitCollection($packagingUnit);
    }

    public function createPackagingUnit(Request $request)
    {
        $this->validate($request,[
            "product_id" => "required|integer",
            "base_unit_quantity" => "required|integer",
            "packaging_unit_name" => "required|string",
            "packaging_quantity" => "required|integer"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "integer" => "Trường :attribute phải là số nguyên",
            "string" => "Trường :attribute phải là chuỗi"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm quy cách đóng gói thành công");
    }

    public function getPackagingUnitById($id)
    {
        $packagingUnit = PackagingUnit::find($id);
        if (empty($packagingUnit)) {
            return $this->responseError("Không tìm thấy quy cách đóng gói với ID: $id");
        }
        return new PackagingUnitResource($packagingUnit);
    }

    public function updatePackagingUnit(Request $request, $id)
    {
        $this->validate($request,[
            "product_id" => "sometimes|required|integer",
            "base_unit_quantity" => "sometimes|required|integer",
            "packaging_unit_name" => "sometimes|required|string",
            "packaging_quantity" => "sometimes|required|integer"
        ],[
            "required" => "Trường :attribute không được trống",
            "integer" => "Trường :attribute phải là số nguyên",
            "string" => "Trường :attribute phải là chuỗi"
        ]);
        $packagingUnit = PackagingUnit::find($id);
        if (empty($packagingUnit)) {
            return $this->responseError("Không tìm thấy quy cách đóng gói với ID: $id");
        }
        try {
            $result = $packagingUnit->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removePackagingUnit($id)
    {
        $packagingUnit = PackagingUnit::find($id);
        if (empty($packagingUnit)) {
            return $this->responseError("Không tìm thấy quy cách đóng gói với ID: $id");
        }
        $packagingUnit->delete();
        return $this->responseSuccess("Xóa quy cách đóng gói thành công");
    }
    
}

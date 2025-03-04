<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Unit;
use App\Http\Controllers\V1\Auth\Resources\Unit\UnitCollection;
use App\Http\Controllers\V1\Auth\Resources\Unit\UnitResource;
use Illuminate\Http\Request;

class UnitController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Unit();
    }
    
    public function getUnits(Request $request)
    {
        $unit = $this->model->search($request->all());
        return new UnitCollection($unit);
    }

    public function createUnit(Request $request)
    {
        $this->validate($request,[
            "code" => "required",
            "name" => "required"
        ],[
            "required" => "Trường :attribute là bắt buộc"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm unit thành công");
    }

    public function getUnitById($id)
    {
        $unit = Unit::find($id);
        if (empty($unit)) {
            return $this->responseError("Không tìm thấy unit với ID: $id");
        }
        return new UnitResource($unit);
    }

    public function updateUnit(Request $request, $id)
    {
        $this->validate($request,[
            "code" => "required",
            "name" => "required"
        ],[
            "required" => "Trường :attribute là bắt buộc"
        ]);
        $unit = Unit::find($id);
        if (empty($unit)) {
            return $this->responseError("Không tìm thấy unit với ID: $id");
        }
        try {
            $result = $unit->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeUnit($id)
    {
        $unit = Unit::find($id);
        if (empty($unit)) {
            return $this->responseError("Không tìm thấy unit với ID: $id");
        }
        $unit->delete();
        return $this->responseSuccess("Xóa unit thành công");
    }
}

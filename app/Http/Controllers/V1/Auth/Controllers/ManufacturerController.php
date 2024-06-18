<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Manufacturer;
use App\Http\Controllers\V1\Auth\Resources\Manufacturer\ManufacturerCollection;
use App\Http\Controllers\V1\Auth\Resources\Manufacturer\ManufacturerResource;
use Illuminate\Http\Request;

class ManufacturerController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Manufacturer();
    }

    public function getManufacturers(Request $request)
    {
        $mfr = $this->model->search($request->all());
        return new ManufacturerCollection($mfr);
    }

    public function createManufacturer(Request $request)
    {
        $this->validate($request,[
            // "name" => "required|unique:manufacturers,name",
            "name" => "required",
            "address" => "sometimes|required",
            "phone" => "sometimes|required",
            "email" => "sometimes|required",
            "website" => "sometimes|required",
            "field" => "sometimes|required",
            "year_established" => "sometimes|required|integer",
            "product_infomation" => "sometimes|required"
        ],[
            // "unique" => "Trường :attribute đã tồn tại",
            "required" => "Trường :attribute là bắt buộc",
            "year_established.integer" => "Năm thành lập phải là số nguyên"
        ]);

        $checkMnf = Manufacturer::where('name',$request->name)->exists();
        if ($checkMnf) {
            return $this->responseError("Nhà sản xuất đã tồn tại");
        }

        $this->model->create($request->all());
        return $this->responseSuccess("Thêm nhà sản xuất thành công");
    }

    public function getManufacturerById($id)
    {
        $mfr = Manufacturer::find($id);
        if (empty($mfr)) {
            return $this->responseError("Không tìm thấy nhà sản xuất với ID: $id");
        }
        return new ManufacturerResource($mfr);
    }

    public function updateManufacturer(Request $request, $id)
    {
        $this->validate($request,[
            "name" => "sometimes|required",
            "address" => "sometimes|required",
            "phone" => "sometimes|required",
            "email" => "sometimes|required",
            "website" => "sometimes|required",
            "field" => "sometimes|required",
            "year_established" => "sometimes|required|integer",
            "product_infomation" => "sometimes|required"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "year_established.integer" => "Năm thành lập phải là số nguyên"
        ]);
        $mfr = Manufacturer::find($id);
        if (empty($mfr)) {
            return $this->responseError("Không tìm thấy nhà sản xuất với ID: $id");
        }
        try {
            $result = $mfr->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeManufacturer($id)
    {
        $mfr = Manufacturer::find($id);
        if (empty($mfr)) {
            return $this->responseError("Không tìm thấy nhà sản xuất với ID: $id");
        }
        // Check có sản phẩm
        if($mfr->products->count()>0){
            return $this->responseError("Cần phải xóa sản phẩm trước khi xóa nhà sản xuất");
        }
        $mfr->delete();
        return $this->responseSuccess("Xóa nhà sản xuất thành công");
    }
}

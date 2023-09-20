<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\ShippingCompany;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyCollection;
use App\Http\Controllers\V1\Auth\Resources\ShippingCompany\ShippingCompanyResource;
use Illuminate\Http\Request;

class ShippingCompanyController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ShippingCompany();
    }
    
    public function getShippingCompanies(Request $request)
    {
        $shippingCompany = $this->model->search($request->all());
        return new ShippingCompanyCollection($shippingCompany);
    }

    public function createShippingCompany(Request $request)
    {
        $this->validate($request,[
            "shipping_company" => "required|string",
            "contact" => "required|string",
            "estimated_shipping_time" => "required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm shipping company thành công");
    }

    public function getShippingCompanyById($id)
    {
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        return new ShippingCompanyResource($shippingCompany);
    }

    public function updateShippingCompany(Request $request, $id)
    {
        $this->validate($request,[
            "shipping_company" => "sometimes|required|string",
            "contact" => "sometimes|required|string",
            "estimated_shipping_time" => "sometimes|required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
        ]);
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        try {
            $result = $shippingCompany->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeShippingCompany($id)
    {
        $shippingCompany = ShippingCompany::find($id);
        if (empty($shippingCompany)) {
            return $this->responseError("Không tìm thấy shipping company với ID: $id");
        }
        $shippingCompany->delete();
        return $this->responseSuccess("Xóa shipping company thành công");
    }
}

<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\OrderStatus;
use App\Http\Controllers\V1\Auth\Resources\OrderStatus\OrderStatusCollection;
use App\Http\Controllers\V1\Auth\Resources\OrderStatus\OrderStatusResource;
use Illuminate\Http\Request;

class OrderStatusController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderStatus();
    }
    
    public function getOrderStatus(Request $request)
    {
        $orderHistory = $this->model->search($request->all());
        return new OrderStatusCollection($orderHistory);
    }

    public function createOrderStatus(Request $request)
    {
        $this->validate($request,[
            "code" => "required|string",
            "name" => "required|string",
            "description" => "required|string",
            "default" => "required|numeric"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "numeric" => "Trường :attribute là kiểu số",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm Order Status thành công");
    }

    public function getOrderStatusById($id)
    {
        $orderStatus = OrderStatus::find($id);
        if (empty($orderStatus)) {
            return $this->responseError("Không tìm thấy order status với ID: $id");
        }
        return new OrderStatusResource($orderStatus);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $this->validate($request,[
            "code" => "sometimes|required|string",
            "name" => "sometimes|required|string",
            "description" => "sometimes|required|string",
            "default" => "sometimes|required|numeric"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "numeric" => "Trường :attribute là kiểu số",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $orderStatus = OrderStatus::find($id);
        if (empty($orderStatus)) {
            return $this->responseError("Không tìm thấy order status với ID: $id");
        }
        try {
            $result = $orderStatus->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeOrderStatus($id)
    {
        $orderStatus = OrderStatus::find($id);
        if (empty($orderStatus)) {
            return $this->responseError("Không tìm thấy order status với ID: $id");
        }
        $orderStatus->delete();
        return $this->responseSuccess("Xóa order status thành công");
    }
}

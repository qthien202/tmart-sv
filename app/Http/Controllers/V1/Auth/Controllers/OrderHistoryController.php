<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\OrderHistory;
use App\Http\Controllers\V1\Auth\Resources\OrderHistory\OrderHistoryCollection;
use App\Http\Controllers\V1\Auth\Resources\OrderHistory\OrderHistoryResource;
use Illuminate\Http\Request;

class OrderHistoryController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderHistory();
    }
    
    public function getOrderHistory(Request $request)
    {
        $orderHistory = $this->model->search($request->all());
        return new OrderHistoryCollection($orderHistory);
    }

    public function createOrderHistory(Request $request)
    {
        $this->validate($request,[
            "user_id" => "required|integer",
            "order_id" => "required|integer",
            "status_code" => "required|string",
            "note" => "sometimes|required|string"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm order history thành công");
    }

    public function getOrderHistoryById($id)
    {
        $orderHistory = OrderHistory::find($id);
        if (empty($orderHistory)) {
            return $this->responseError("Không tìm thấy order history với ID: $id");
        }
        return new OrderHistoryResource($orderHistory);
    }

    public function updateOrderHistory(Request $request, $id)
    {
        $this->validate($request,[
            "user_id" => "sometimes|required|integer",
            "order_id" => "sometimes|required|integer",
            "status_code" => "sometimes|required|string"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $orderHistory = OrderHistory::find($id);
        if (empty($orderHistory)) {
            return $this->responseError("Không tìm thấy order history với ID: $id");
        }
        try {
            $result = $orderHistory->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeOrderHistory($id)
    {
        $orderHistory = OrderHistory::find($id);
        if (empty($orderHistory)) {
            return $this->responseError("Không tìm thấy order history với ID: $id");
        }
        $orderHistory->delete();
        return $this->responseSuccess("Xóa order history thành công");
    }
}

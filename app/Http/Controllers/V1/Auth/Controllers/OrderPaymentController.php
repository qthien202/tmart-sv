<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\OrderPayment;
use App\Http\Controllers\V1\Auth\Resources\OrderPayment\OrderPaymentCollection;
use App\Http\Controllers\V1\Auth\Resources\OrderPayment\OrderPaymentResource;
use Illuminate\Http\Request;

class OrderPaymentController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderPayment();
    }
    
    public function getOrderPayments(Request $request)
    {
        $orderHistory = $this->model->search($request->all());
        return new OrderPaymentCollection($orderHistory);
    }

    public function createOrderPayment(Request $request)
    {
        $this->validate($request,[
            "order_id" => "required|integer",
            "payment_method" => "required|string",
            "payment_status" => "required|string",
            "amount" => "required|string"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm order payment thành công");
    }

    public function getOrderPaymentById($id)
    {
        $orderPayment = OrderPayment::find($id);
        if (empty($orderPayment)) {
            return $this->responseError("Không tìm thấy order payment với ID: $id");
        }
        return new OrderPaymentResource($orderPayment);
    }

    public function updateOrderPayment(Request $request, $id)
    {
        $this->validate($request,[
            "order_id" => "sometimes|required|integer",
            "payment_method" => "sometimes|required|string",
            "payment_status" => "sometimes|required|string",
            "amount" => "sometimes|required|string"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $orderPayment = OrderPayment::find($id);
        if (empty($orderPayment)) {
            return $this->responseError("Không tìm thấy order payment với ID: $id");
        }
        try {
            $result = $orderPayment->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeOrderPayment($id)
    {
        $orderPayment = OrderPayment::find($id);
        if (empty($orderPayment)) {
            return $this->responseError("Không tìm thấy order payment với ID: $id");
        }
        $orderPayment->delete();
        return $this->responseSuccess("Xóa order payment thành công");
    }
}

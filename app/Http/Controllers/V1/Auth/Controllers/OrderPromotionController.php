<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\OrderPromotion;
use App\Http\Controllers\V1\Auth\Resources\OrderPromotion\OrderPromotionCollection;
use App\Http\Controllers\V1\Auth\Resources\OrderPromotion\OrderPromotionResource;
use Illuminate\Http\Request;

class OrderPromotionController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderPromotion();
    }
    
    public function getOrderPromotions(Request $request)
    {
        $orderPromotion = $this->model->search($request->all());
        return new OrderPromotionCollection($orderPromotion);
    }

    public function createOrderPromotion(Request $request)
    {
        $this->validate($request,[
            "order_id" => "required|integer",
            "name" => "required|string",
            "discount_amount" => "required",
            "description" => "required|string",
            "start_date" => "required",
            "end_date" => "required",
            "promotion_type" => "required|string",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $this->model->create($request->all());
        return $this->responseSuccess("Thêm order promotion thành công");
    }

    public function getOrderPromotionById($id)
    {
        $orderPromotion = OrderPromotion::find($id);
        if (empty($orderPromotion)) {
            return $this->responseError("Không tìm thấy order promotion với ID: $id");
        }
        return new OrderPromotionResource($orderPromotion);
    }

    public function updateOrderPromotion(Request $request, $id)
    {
        $this->validate($request,[
            "order_id" => "sometimes|required|integer",
            "name" => "sometimes|required|string",
            "discount_amount" => "sometimes|required",
            "description" => "sometimes|required|string",
            "start_date" => "sometimes|required",
            "end_date" => "sometimes|required",
            "promotion_type" => "sometimes|required|string",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên"
        ]);
        $orderPromotion = OrderPromotion::find($id);
        if (empty($orderPromotion)) {
            return $this->responseError("Không tìm thấy order promotion với ID: $id");
        }
        try {
            $result = $orderPromotion->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    public function removeOrderPromotion($id)
    {
        $orderPromotion = OrderPromotion::find($id);
        if (empty($orderPromotion)) {
            return $this->responseError("Không tìm thấy order payment với ID: $id");
        }
        $orderPromotion->delete();
        return $this->responseSuccess("Xóa order payment thành công");
    }
}

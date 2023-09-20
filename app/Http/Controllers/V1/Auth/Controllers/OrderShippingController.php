<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Order;
use App\Http\Controllers\V1\Auth\Models\OrderShipping;
use App\Http\Controllers\V1\Auth\Models\OrderShippingDetail;
use App\Http\Controllers\V1\Auth\Resources\OrderShipping\OrderShippingCollection;
use App\Http\Controllers\V1\Auth\Resources\OrderShipping\OrderShippingResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderShippingController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new OrderShipping();
    }
    
    public function getOrderShippings(Request $request)
    {
        $orderShipping = $this->model->search($request->all());
        return new OrderShippingCollection($orderShipping);
    }

    public function createOrderShipping(Request $request)
    {
        $this->validate($request,[
            "order_id" => "required|integer",
            "status" => "required|string",
            "shipping_date" => "required|date",
            "estimated_delivery_date" => "required|date",
            "shipping_description" => "sometimes|required|string"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên",
            "date" => "Trường :attribute phải là định dạng date (yyyy-mm-dd)"
        ]);
        DB::beginTransaction();
        try {
            $orderShipping = OrderShipping::create([
                'order_id' => $request->order_id,
                'status' => $request->status,
                'shipping_date' => $request->shipping_date,
                'estimated_delivery_date' => $request->estimated_delivery_date,
            ]);
            $orderShippingDetail = new OrderShippingDetail();
            $orderShippingDetail->shipping_description = $request->shipping_description;
            $orderShippingDetail->order_shipping_id = $orderShipping->id;
            $orderShippingDetail->save();
            DB::commit();
            return $this->responseSuccess("Thêm order Shipping thành công");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th->getMessage());
        }
        
    }

    public function getOrderShippingById($id)
    {
        $orderShipping = OrderShipping::find($id);
        if (empty($orderShipping)) {
            return $this->responseError("Không tìm thấy order Shipping với ID: $id");
        }
        return new OrderShippingResource($orderShipping);
    }

    public function updateOrderShipping(Request $request, $id)
    {
        $this->validate($request,[
            "order_id" => "sometimes|required|integer",
            "status" => "sometimes|required|string",
            "shipping_date" => "sometimes|required|date",
            "estimated_delivery_date" => "sometimes|required|date",
            "shipping_description" => "sometimes|required|string",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là kiểu chuỗi",
            "integer" => "Trường :attribute phải là số nguyên",
            "date" => "Trường :attribute phải là định dạng date (yyyy-mm-dd)"
        ]);
        $orderShipping = OrderShipping::find($id);

        if (empty($orderShipping)) {
            return $this->responseError("Không tìm thấy order Shipping với ID: $id");
        }

        $orderShippingDetail = OrderShippingDetail::where("order_shipping_id",$id)->first();
        if (empty($orderShippingDetail)) {
            return $this->responseError("Không tìm thấy order Shipping Detail với ID Shipping: $id");
        }

        DB::beginTransaction();
        try {
            if ($request->has('order_id')){
                $orderShipping->order_id = $request->order_id;
            }
            if ($request->has('status')){
                $orderShipping->status = $request->status;
            }
            if ($request->has('shipping_date')){
                $orderShipping->shipping_date = $request->shipping_date;
            }
            if ($request->has('estimated_delivery_date')){
                $orderShipping->estimated_delivery_date = $request->estimated_delivery_date;
            }

            if ($request->has('shipping_description')){
                $orderShippingDetail->shipping_description = $request->shipping_description;
                $orderShippingDetail->save();
            }
            $orderShipping->save();
            
            DB::commit();
            return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th->getMessage());
        }
    }

    public function removeOrderShipping($id)
    {
        $orderShipping = OrderShipping::find($id);
        if (empty($orderShipping)) {
            return $this->responseError("Không tìm thấy order Shipping với ID: $id");
        }
        $orderShipping->orderShippingDetail()->delete();
        $orderShipping->delete();
        return $this->responseSuccess("Xóa order Shipping thành công");
    }
}

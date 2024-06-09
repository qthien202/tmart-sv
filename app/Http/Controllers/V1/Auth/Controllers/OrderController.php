<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

// use App\Http\Controllers\V1\Auth\Models\Price;
// use App\Http\Controllers\V1\Auth\Models\PriceDetail;
// use App\Http\Controllers\V1\Auth\Resources\Price\PriceCollection;
// use App\Http\Controllers\V1\Auth\Resources\Price\PriceResource;

use App\Http\Controllers\V1\Auth\Models\Order;
use App\Http\Controllers\V1\Auth\Models\OrderDetail;
use App\Http\Controllers\V1\Auth\Models\OrderPayment;
use App\Http\Controllers\V1\Auth\Models\OrderStatus;
use App\Http\Controllers\V1\Auth\Resources\Order\OrderCollection;
use App\Http\Controllers\V1\Auth\Resources\Order\OrderResource;
use App\Http\Controllers\V1\Normal\Models\Cart;
use App\SERVICE;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Order();
    }

    public function getOrderByUserID(Request $request){
        $input = $request->all();
        $input["user_id"] = SERVICE::getCurrentUserId();
        $order = $this->model->search($input);
        return new OrderCollection($order);
    }

    public function getOrders(Request $request){
        $order = $this->model->search($request->all());
        return new OrderCollection($order);
        // return Order::find(1)->orderDetails()->get();
    }

    // Xác nhận đơn hàng
    public function confirmOrder(Request $request){

        $userID = SERVICE::getCurrentUserId();

        $this->validate($request,[
            "cart_id" => "required|integer|exists:carts,id",
            "shipping_company_id" => "required|integer|exists:shipping_companies,id",
            "note" => "string",
            "shipping_address" => "sometimes|required|string",
            "billing_address" => "required|string",
            // "payment_uid" => "required"
        ]);
        $cart = Cart::find($request->cart_id);
        if (is_null($cart)) {
            return $this->responseError('Không tìm thấy giỏ hàng');
        }
        if (!($userID == $cart->user_id)) {
            return $this->responseError('Sai thông tin');
        }
        // $order = $this->model->create($request->all());
        // $orderDetail = $order->orderDetails()->create($request->all());
        DB::beginTransaction();
        try {
            $order = $this->model->create([
                'user_id' => $userID,
                'order_number' => strtoupper($userID . 'ORD' .  uniqid()),
                'info_total_amount' => $cart->info,
                'status_code' => "pending",
                'shipping_company_id' => $request->shipping_company_id,
                'name' => $cart->name,
                'phone' => $cart->phone,
                'payment_uid' => $request->payment_uid,
                'coupon_code' => $cart->coupon_code,
                'voucher_code' => $cart->voucher_code,
                'note' => $request->note,
                'recipient_address' => $cart->address,
                'shipping_address' => is_null($request?->shipping_address) ? $cart->address : $request?->shipping_address,
                'billing_address' => $request->billing_address,
                'free_item' => $cart->free_item,
                'order_date' => Carbon::now(),
    
            ]);
            
            $total = array_values(array_filter($cart->info,function($e){
                return $e['code']=="total";
            }))[0]??null;
            $total = $total['value'];
            $orderPayment = new OrderPayment();
            $orderPayment->order_id = $order->id;
            $orderPayment->amount = $total;
            if ($request->payment == "cod") {
                $orderPayment->payment_method = "Thanh toán COD";
                $orderPayment->payment_status = "Chờ xác nhận";

            }
            if ($request->payment == "vnpay") {
                $orderPayment->payment_method = "Thanh toán bằng VNPAY";
                $orderPayment->payment_status = "Chờ xác nhận";
            }
            $orderPayment->save();

            $cart->cartDetails->map(function ($item) use (&$order){
                $order->orderDetails()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'option' => $item->option,
                    'subtotal' => $item->total,
                ]);
            });
            $order->orderHistories()->create([
                'user_id' => $userID,
                'order_id' => $order->id,
                'status_id' => $order->status_code,
                'note' => $order->note,
            ]);
            $product_name = $cart->cartDetails[0]->product->product_name; // Sản phẩm đầu tiên để hiện thông báo
            $thumpnail_url = $cart->cartDetails[0]->product->thumpnail_url; // Sản phẩm đầu tiên để hiện thông báo
            $amountProduct = $cart->cartDetails->count();
            $cart->delete();
            $cart->cartDetails()->delete();
            DB::commit();
            if ($amountProduct > 1) {
                $content = 'Đặt '.$product_name.' và '.$amountProduct.' sản phẩm khác thành công';
            }else{
                $content = 'Đặt '.$product_name.' thành công';
            }
            $this->notificationToDevice('Đặt hàng thành công',$content,$thumpnail_url);
            return $this->responseSuccess('Thêm thành công',["order_id"=>$order->id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th->getMessage());
        }
       

    }
    
    public function removeOrder($id){
        $order = Order::find($id);
        if (empty($order)) {
            return $this->responseError('Không tìm thấy id');
        }
        $order->orderDetails()->delete();
        $order->delete();
        return $this->responseSuccess('Xóa thành công');
    }

    public function getOrderById($id){
        $order = Order::find($id);
        if (empty($order)) {
            return $this->responseError("Không tìm thấy");
        }
        return new OrderResource($order);
    }

    public function updateOrder(Request $request,$id){
        $this->validate($request,[
            "user_id" => "sometimes|required|integer|exists:users,id",
            "order_number" => "sometimes|required|string|max:255",
            "info_total_amount" => "sometimes|json",
            "status_code" => "sometimes|string|max:255|exists:order_status,code",
            "shipping_companie_id" => "sometimes|integer|exists:shipping_companies,id",
            "name" => "sometimes|string|max:255",
            "phone" => "sometimes|string|max:255",
            "payment_uid" => "sometimes|string|max:255|exists:order_payments,id",
            "coupon_code" => "sometimes|string|max:255|exists:coupons,coupon_code",
            "voucher_code" => "sometimes|string|max:255|exists:vouchers,voucher_code",
            "note" => "sometimes|string|max:255",
            "recipient_address" => "sometimes|string|max:255",
            "shipping_address" => "sometimes|string",
            "billing_address" => "sometimes|string",
            "free_item" => "sometimes|json",
        ],[
            "required" => "Trường này bắt buộc",
            "exists" => ":attribute không tồn tại"
        ]);

        $order = Order::find($id);
        if (empty($order)) {
            $this->responseError("Không tìm thấy đơn hàng");
        }

        if (empty($request->all())) {
            return $this->responseError("Không có thay đổi");
        }

        // Orders
        if ($request->has('user_id')){
            $order->user_id = $request->user_id;
        }
        if ($request->has('order_number')){
            $order->order_number = $request->order_number;
        }
        if ($request->has('info_total_amount')){
            $order->info_total_amount = $request->info_total_amount;
        }
        if ($request->has('status_code')){
            $order->status_code = $request->status_code;
        }
        if ($request->has('shipping_companie_id')){
            $order->shipping_companie_id = $request->shipping_companie_id;
        }
        if ($request->has('name')){
            $order->name = $request->name;
        }
        if ($request->has('phone')){
            $order->phone = $request->phone;
        }
        if ($request->has('payment_uid')){
            $order->payment_uid = $request->payment_uid;
        }
        if ($request->has('coupon_code')){
            $order->coupon_code = $request->coupon_code;
        }
        if ($request->has('voucher_code')){
            $order->voucher_code = $request->voucher_code;
        }
        if ($request->has('note')){
            $order->note = $request->note;
        }
        if ($request->has('recipient_address')){
            $order->recipient_address = $request->recipient_address;
        }
        if ($request->has('shipping_address')){
            $order->shipping_address = $request->shipping_address;
        }
        if ($request->has('billing_address')){
            $order->billing_address = $request->billing_address;
        }
        if ($request->has('free_item')){
            $order->free_item = $request->free_item;
        }

        $order->save();
        return $this->responseSuccess("Thành công");
    }

    public function updateOrderDetail(Request $request, $id){
        $this->validate($request,[
            "product_id" => "required|integer|exists:products,id",
            "quantity" => "required|integer",
            "price" => "required|numeric",
            "option" => "required|array",
            "subtotal" => "required|numeric",
        ],[
            "required" => ":attribute là bắt buộc",
            "exists" => ":attribute không tồn tại"
        ]);

        $orderDetail = OrderDetail::find($id);
        if (empty($orderDetail)) {
            $this->responseError("Không tìm thấy đơn hàng chi tiết");
        }

        if (empty($request->all())) {
            return $this->responseError("Không có thay đổi");
        }

        // Order Details
        if ($request->has('product_id')){
                $orderDetail->product_id = $request->product_id;
        }
        if ($request->has('quantity')){
                $orderDetail->quantity = $request->quantity;
        }
        if ($request->has('price')){
                $orderDetail->price = $request->price;
        }
        if ($request->has('option')){
                $orderDetail->option = $request->option;
        }
        if ($request->has('subtotal')){
                $orderDetail->subtotal = $request->subtotal;
        }
        
        $orderDetail->save();
        return $this->responseSuccess("Thành công");
    }

    // public function updateOrderStatus($id, Request $request){
    //     $this->validate($request,[
    //         "status_code" => "required|exists:order_status,code"
    //     ],[
    //         "required" => "Trường này bắt buộc",
    //         "status_code.exists" => "Status code không tồn tại"
    //     ]);
        
    //     $order = Order::find($id);
    //     if (empty($order)) {
    //         $this->responseError("Không tìm thấy đơn hàng");
    //     }
    //     $order->status_code = $request->status_code;
    //     $order->save();
    //     return $this->responseSuccess("Thành công");
    // }

    // Hủy đơn hàng (set status = cancel)
    public function cancelOrder($id){
        // $this->validate($request,[
        //     "id" => "required"
        // ]);
        $order = Order::find($id);
        $userID = SERVICE::getCurrentUserId();
        if (($order->user_id != $userID)) {
            return $this->responseError("Bạn không có quyền hủy đơn này");
        }
        $orderNumber = $order->order_number;
        $thumpnailUrl = $order->orderDetails[0]->product->thumpnail_url;
        if (empty($order)) {
            return $this->responseError("Không tìm thấy đơn hàng");
        }
        try {
            $order->status_code = "cancelled";
            $order->save();
            $this->notificationToDevice('Hủy đơn hàng','Đơn hàng '.$orderNumber.' đã được hủy thành công', $thumpnailUrl, $order->id);
            return $this->responseSuccess("Hủy thành công");
        } catch (\Throwable $th) {
            return $this->responseSuccess("Hủy thất bại, error: ",$th->getMessage());
        }
        
    }

}

<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Http\Controllers\V1\Normal\Models\Cart;
use App\Http\Controllers\V1\Normal\Models\CartDetail;
use App\Http\Controllers\V1\Normal\Models\Coupon;
use App\Http\Controllers\V1\Normal\Models\Session;
use App\SERVICE;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Foundation\Handle;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\Http\Controllers\V1\Normal\Models\Condition;
use App\Http\Controllers\V1\Normal\Models\Voucher;
use App\Http\Controllers\V1\Normal\Resources\Cart\CartResource;

class CartController extends BaseController
{
    protected $condition;
    public function __construct() {
        $this->condition = new Condition();
    }
    public function addToCart(Request $request)
    {

        $userID = SERVICE::getCurrentUserId();

        $this->validate($request, [
            "guest_session" =>  [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
            "product_id" => "required|exists:products,id",
            "quantity" => "sometimes|required|integer|min:1"
        ], [
            "guest_session.required" => "Trường guest_session là bắt buộc nếu không có đăng nhập",
            "guest_session.exists" => "Không tìm thấy Guest",
            "product_id.required" => "Trường product_id là bắt buộc",
            "quantity.integer" => "Số lượng phải là số nguyên",
            "quantity.required" => "Số lượng không được để trống",
            "product_id.exists" => "ID sản phẩm không tồn tại",
            "quantity.min" => "Số lượng phải là số lớn hơn 0"
        ]);

        // Cả 2 cùng truyền (có user_id: đã đăng nhập) -> ưu tiên user_id
        // $checkLogin = auth()->check();
        $checkLogin = !is_null($userID);
        $quantity = !isset($request->quantity) ? 1 : $request->quantity;
        $productID = $request->product_id;

        // Thêm giỏ hàng nếu giỏ hàng chưa có
        if ($checkLogin) {
            $cart = Cart::where("user_id", $userID)->first();
        } else {
            $guestSession = $request->guest_session;
            $guestID = Session::where("session_id", $guestSession)->value("id");
            $cart = Cart::where("guest_id", $guestID)->first();
        }
        
        DB::beginTransaction();
        try {
            if (!is_null($cart)) { // Nếu có giỏ hàng của người đó -> không tạo thêm giỏ hàng mới
                $cartID = $cart->id;
                $cartDetail = Cart::find($cartID)->cartDetails->where("product_id", $productID)->first();
                if (!is_null($cartDetail)) { // Có tồn tại sản phẩm trong giỏ hàng -> Update số lượng thêm 1
                    // Set Info
                    $cartDetail->quantity += $quantity;
                    $cartDetail->product_name = $cartDetail->product->product_name;
                    // $cartDetail->total = $cartDetail->product->price * $cartDetail->quantity; //giá lấy mặc định nếu k có biến thể (chưa có bảng biến thể)
                    $cartDetail->save();
                    CartDetail::updateTotal($cartDetail->id);
                    Cart::setInfoCart($cartID);
                    DB::commit();
                    return $this->responseSuccess("Cập nhật số lượng sản phẩm trong giỏ hàng thành công!");
                }
            } else {
                // Không có giỏ hàng của người đó -> tạo giỏ hàng
                $cart = new Cart();
                if ($checkLogin) {
                    $cart->user_id = $userID;
                } else {
                    $cart->guest_id = $guestID;
                }
                $cart->save();
            };

            // Sản phẩm chưa tồn tại trong giỏ hàng chi tiết
            $cartDetail = new CartDetail();
            $cartDetail->cart_id = $cart->id;
            $cartDetail->product_id = $productID;
            $cartDetail->product_name = $cartDetail->product->product_name;
            $cartDetail->quantity = $quantity;
            // $cartDetail->total = $cartDetail->product->price * $cartDetail->quantity;
            $cartDetail->save();
            CartDetail::updateTotal($cartDetail->id);
            Cart::setInfoCart($cart->id);
            DB::commit();
            return $this->responseSuccess("Thêm vào giỏ hàng thành công!");
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->responseError($th->getMessage());
        }
    }
    //Update bảng Carts theo id
    public function updateCart($id, Request $request)
    {
        $userID = SERVICE::getCurrentUserId();
        // Input: session/user_id hoặc cart_id
        $cart = Cart::find($id);
        if(is_null($cart)){
            return $this->responseError("Không tìm thấy giỏ hàng với ID $id");
        }

        $this->validate($request, [
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
            "name" => "sometimes|required|string|max:100",
            "phone" => "sometimes|required|string|max:11",
            "payment_method" => "sometimes|required|string|max:20",
            "payment_status" => "sometimes|required|numeric|max:1",
            "coupon_code" => "sometimes|string|max:20|exists:coupons,coupon_code",
            "voucher_code" => "sometimes|string|max:20|exists:vouchers,voucher_code",
            "street_no" => "sometimes|required|string",
            "ward_id" => "sometimes|required|numeric",
            "district_id" => "sometimes|required|numeric",
            "city_id" => "sometimes|required|numeric",
            "address" => "sometimes|required|string",
            "free_item" => "sometimes|required|json",
            "info" => "sometimes|required|json"
        ],[
            "required" => "Trường này không được trống",
            "required_with" => "Trường này phải có nếu các trường liên quan có",
            "string" => "Trường này phải là chuỗi",
            "numeric" => "Trường này phải là số",
            "in" => "Trường này chỉ được là P hoặc F",
            "max" => "Trường này tối đa :max ký tự",
            "json" => "Trường này chỉ được truyền json",
            "exists" => ":attribute không tồn tại"
        ]);
        // $checkLogin = !is_null($userID);

        // if ($checkLogin) {
        //     //user
        //     $check = $cart->user_id;
        //     if(!($check == $userID)){
        //         return $this->responseError("Không đúng thông tin");
        //     };
        // }else{
        //     $guestSession = $request->session;
        //     $guestID = Session::where("session_id", $guestSession)->value("id");
        //     $check = $cart->guest_id;
        //     if (!($check == $guestID)) {
        //         return $this->responseError("Không đúng thông tin");
        //     }
        // }
        $handle = new Handle($cart);
        $message = $handle->checkSessionOrUser($userID,$request->session);
        if (!empty($message)) {
            return $this->responseError($message);
        }

        // Update cả 2 voucher/coupon
        if ((!is_null($request->coupon_code)) && (!is_null($request->voucher_code))) {
            return $this->responseError("Coupon và Voucher không thể sử dụng cùng nhau");
        }

        try {
            $result = $cart->update($request->all());
            Cart::setInfoCart($cart->id);
            if ($result) {
                return $this->responseSuccess("Cập nhật thành công",$request->keys());
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }

    // thông báo thành công
    // Xóa cartdetail
    public function removeCart($id, Request $request)
    {
        // $cart = Cart::withTrashed()->find($id)->restore();
        $userID = SERVICE::getCurrentUserId();
        $this->validate($request,[
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        ],[
            "required"=>":attribute Không để trống",
            "exists"=>"Sai mã phiên của bạn",
        ]);
        
        $cart = Cart::find($id);
        if(empty($cart)){
            $message = "Không tìm thấy giỏ hàng có ID: $id";
            return $this->responseError($message);
        }

        // Check hợp lệ thông tin
        $handle = new Handle($cart);
        $message = $handle->checkSessionOrUser($userID,$request->session);
        if (!empty($message)) {
            return $this->responseError($message);
        }

        $cart->deleteCartDetails();
        $cart->delete();
        $message = "Xóa giỏ hàng thành công!";
        return $this->responseSuccess($message);
    }
    // update bảng CartDetails theo id
    public function updateCartDetail($id,Request $request)
    {
        // check session or userid
        $userID = SERVICE::getCurrentUserId();
        $cartDetail = CartDetail::find($id);
        $this->validate($request,[
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
            "product_id" => "sometimes|required|numeric|exists:products,id",
            "quantity" => "sometimes|required|integer|min:1",
            "price" => "sometimes|required|numeric",
            "option" => "sometimes|required|json",
            "total" => "sometimes|required|numeric",
        ],[
            "numeric" => "Trường này phải là số",
            "required" => "Trường này là bắt buộc",
            "integer" => "Trường này phải là số nguyên",
            "json" => "Trường này chỉ được truyền json",
            "product_id.exists" => "Mã sản phẩm không tồn tại",
            "session.exists" =>"Mã phiên không đúng!",
            "quantity.min" => "Số lượng phải là số lớn hơn 0"
        ]);
        if(empty($cartDetail)){
            return $this->responseError("Không tìm thấy Cart Detail với ID: $id");
        }

        $handle = new Handle($cartDetail->cart);
        $message = $handle->checkSessionOrUser($userID,$request->session);
        if (!empty($message)) {
            return $this->responseError($message);
        }

        try {
            $result = $cartDetail->update($request->all());
            $cartDetail->product_name = $cartDetail->product->product_name;
            $cartDetail->save();
            CartDetail::updateTotal($cartDetail->id);
            Cart::setInfoCart($cartDetail->cart_id);
            if ($result) {
                return $this->responseSuccess("Thành công");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }

    }
    public function removeCartDetail($id,Request $request){
        // $cart = Cart::withTrashed()->find($id)->restore();
        $userID = SERVICE::getCurrentUserId();
        $this->validate($request,[
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        ],[
            "required"=>":attribute Không để trống",
            "exists"=>"Sai mã phiên của bạn",
        ]);
        
        $cartDetail = CartDetail::find($id);
        if(empty($cartDetail)){
            return $this->responseError("Không tìm thấy Cart Detail với ID: $id");
        }

        $handle = new Handle($cartDetail->cart);
        $message = $handle->checkSessionOrUser($userID,$request->session);
        if (!empty($message)) {
            return $this->responseError($message);
        }

        $cartDetail->delete();
        Cart::setInfoCart($cartDetail->cart_id);
        $message = "Xóa sản phẩm trong giỏ hàng thành công!";
        return $this->responseSuccess($message);
    }
    public function getCart(Request $request)
    {
        //cckt giống coupon
        //coupon condition: điều kiện hiển thị coupon: đơn hàng từ| tối đa
        // mua sản phẩm a mới dc apply, tổng tiên giỏ hàng > số tiền, mua đơn hàng đầu tiên, đặt hàng qua mobile, áp dụng toàn sàn, cho nhóm khách hàng(user/gession), áp dụng theo khu vực.
        // ctkm: sinh nhật tri ân, kiểm profile tháng sn, chương trình mua x tặng y(freeitem) có số lượng đủ giao không, tặng bao nhiêu khách hàng, check còn số lượng trước khi thêm vào đơn hàng
        // coupon voucher -> bảng giá(thời gian hiệu lực, giá tới tháng 9 -> giá mới) + theo attribute/ cố định
        $userID = null;
        $userID = SERVICE::getCurrentUserId();
        // Lấy giỏ hàng ưu tiên user
        $this->validate($request,[
            "guest_session" =>  [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        ],[
            "guest_session.required" => "Trường guest_session là bắt buộc nếu không có đăng nhập",
            "guest_session.exists" => "Không tìm thấy Guest",
        ]);

        $guestSession = $request->guest_session;
        $guestID = Session::where("session_id", $guestSession)->value("id");
        
        // Lấy giỏ hàng của user hoặc guest (ưu tiên user)
        $cart = Cart::where(function ($query) use ($userID,$guestID){
            if (!is_null($userID)){
                $query->where("user_id", $userID);
            }else{
                // Nếu user id có thì không quan tâm tới guest
                $query->where("guest_id", $guestID);
            }
        })->first();
        if (is_null($cart)) {
            // $message = "Không tìm thấy giỏ hàng";
            // return $this->responseError($message);
            return response()->json(["data"=>[]]);
        }
        // Check coupon/voucher hết hạn -> remove khỏi cart
        $handle = new Handle($cart);
        $handle->check(); 
        Cart::setInfoCart($cart->id);
        return new CartResource($cart);
    }

    public function getCoupons(){
        return $this->responseSuccess(null,Coupon::all());
    }
    public function getVouchers(){
        return $this->responseSuccess(null,Voucher::all());
    }
    // Thêm coupon vào giỏ hàng
    public function addCoupon(Request $request)
    {
        
        $userID = SERVICE::getCurrentUserId();
        $this->validate($request,[
            "session" =>  [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
            "cart_id" => "required",
            "coupon_code" => "required|string|max:20|exists:coupons,coupon_code",
        ],[
            "required" => "Trường này không được trống",
            "string" => "Trường này phải là chuỗi",
            "exists" => ":attribute không tồn tại",
            "max" => "Trường này tối đa :max ký tự"
        ]);

        try {
            $cart = Cart::find($request->cart_id);

            if (empty($cart)) {
                return $this->responseError("Không tìm thấy giỏ hàng");
            }

            $handle = new Handle($cart);
            $message = $handle->checkSessionOrUser($userID,$request->session);
            if (!empty($message)) {
                return $this->responseError($message);
            }

            if (!is_null($cart->voucher_code)) {
                $cart->voucher_code = null;
                $cart->save();
            }
            $couponCode = $request->coupon_code;
            $cart->coupon_code = $couponCode;
            $cart->save();

            $handle = new Handle($cart);
            $result = $handle->check(1);
            Cart::setInfoCart($cart->id);
            if (!empty($result)) {
                return $this->responseError($result[0]);
            }

            return $this->responseSuccess("Thêm coupon thành công");
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }
    public function removeCoupon($id, Request $request)
    {
        $userID = SERVICE::getCurrentUserId();
        $this->validate($request,[
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        ],[
            "required"=>":attribute Không để trống",
            "exists"=>"Sai mã phiên của bạn",
        ]);
        // Id giỏ hàng cần xóa coupon
        try {
            $cart = Cart::find($id);
            if (empty($cart)) {
                return $this->responseError("Không tìm thấy giỏ hàng");
            }

            $handle = new Handle($cart);
            $message = $handle->checkSessionOrUser($userID,$request->session);
            if (!empty($message)) {
                return $this->responseError($message);
            }

            $cart->coupon_code = null;
            $cart->save();
            Cart::setInfoCart($cart->id);
            return $this->responseSuccess("Xóa coupon thành công");
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }
    public function addVoucher(Request $request)
    {
        $userID = SERVICE::getCurrentUserId();

        $this->validate($request,[
            "session" =>  [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
            "cart_id" => "required",
            "voucher_code" => "required|string|max:20|exists:vouchers,voucher_code",
        ],[
            "required" => "Trường này không được trống",
            "exists" => ":attribute không tồn tại",
            "string" => "Trường này phải là chuỗi",
            "max" => "Trường này tối đa :max ký tự"
        ]);
        try {
            $cart = Cart::find($request->cart_id);
            if (empty($cart)) {
                return $this->responseError("Không tìm thấy giỏ hàng");
            }

            $handle = new Handle($cart);
            $message = $handle->checkSessionOrUser($userID,$request->session);
            if (!empty($message)) {
                return $this->responseError($message);
            }

            if (!is_null($cart->coupon_code)) {
                $cart->coupon_code = null;
                $cart->save();
            }
            $voucherCode = $request->voucher_code;
            $cart->voucher_code = $voucherCode;
            $cart->save();
            
            $handle = new Handle($cart);
            $result = $handle->check(2);
            Cart::setInfoCart($cart->id);

            if (!empty($result)) {
                return $this->responseError($result[0]);
            }

            return $this->responseSuccess("Thêm voucher thành công");
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }
    public function removeVoucher($id, Request $request)
    {
        $userID = SERVICE::getCurrentUserId();
        $this->validate($request,[
            'session' => [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        ],[
            "required"=>":attribute Không để trống",
            "exists"=>"Sai mã phiên của bạn",
        ]);
        // Id giỏ hàng cần xóa voucher
        try {
            $cart = Cart::find($id);
            if (empty($cart)) {
                return $this->responseError("Không tìm thấy giỏ hàng");
            }

            $handle = new Handle($cart);
            $message = $handle->checkSessionOrUser($userID,$request->session);
            if (!empty($message)) {
                return $this->responseError($message);
            }

            $cart->voucher_code = null;
            $cart->save();
            Cart::setInfoCart($cart->id);
            return $this->responseSuccess("Xóa voucher thành công");
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }
    
    public function addCartToUser(Request $request)
    {
        $this->validate($request,[
            "guest_session" => "required|exists:sessions,session_id"
        ],[
            "required" => "Trường này bắt buộc",
            "exists"   => "Session không tồn tại"
        ]);

        // $userID = SERVICE::getCurrentUserId();
        $guestSession = $request->guest_session;
        $guestID = Session::where("session_id",$guestSession)->value("id");

        // Kiểm tra guest có giỏ hàng
        $check = Cart::where("guest_id",$guestID)->exists();
        if (!$check) {
            return $this->responseError("Guest chưa có giỏ hàng");
        }

        $cart = Cart::where("guest_id",$guestID)->first()->cartDetails()->get();
        
        // $cart gồm nhiều cartdetails -> foreach
        foreach ($cart as $cartDetail) {
            $productID = $cartDetail->product_id;
            $quantity = $cartDetail->quantity;
            // "price": null,
            // "option": null,
            // "total": null
            $data = [
                "product_id" => $productID,
                "quantity" => $quantity
            ];
            $request = new Request($data);
            $this->addToCart($request);
        }
        $sessionController = new SessionController();

        // Xóa cart của guest
        $cartID = Session::where("session_id",$guestSession)->first()->cart()->value("id");
        $cart = Cart::find($cartID);

        $handle = new Handle($cart);
        $message = $handle->checkSessionOrUser(null,$guestSession);
        if (!empty($message)) {
            return $this->responseError($message);
        }
        $cart->deleteCartDetails();
        $cart->delete();

        // Xóa guest
        $data = [
            "guest_session" => $guestSession
        ];
        $request = new Request($data);
        $sessionController->removeSession($request);
        return;
        
    }
}

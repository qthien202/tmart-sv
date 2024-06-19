<?php
namespace App\Foundation;

use App\Http\Controllers\V1\Auth\Models\Order;
use App\Http\Controllers\V1\Normal\Models\Coupon;
use App\Http\Controllers\V1\Normal\Models\Session;
use App\Http\Controllers\V1\Normal\Models\Voucher;
use App\SERVICE;
use Carbon\Carbon;

class Handle{
    
    protected $cart;

    public function __construct($cart)
    {
        $this->cart = $cart;
    }

    public function check($option=null){ 
        /**
         * @param Option = 1: Chỉ check coupon - Option = 2: Chỉ check voucher - dùng cho addVoucher/addCoupon
         *  */ 
        // return;

        // if ($this->isMobileDevice($userAgent)) {
        $cart = $this->cart;
        $result = [];
        $cartDetails = $cart->cartDetails;

        // COUPON
        if ((!is_null($cart->coupon_code)) && ($option != 2)) {
            $couponCode = $cart->coupon_code;
            $coupon = Coupon::find($couponCode);
            $now = Carbon::now();

            $result = [];
            $condition = $coupon->conditions;
            if(!is_null($condition)){
                // Giá trị đơn hàng tối thiểu
                foreach ($cart->info as $info) {
                    if ($info["code"]=="sub_total") {
                        // Tổng giỏ hàng
                        $totalPrice = $info["value"];
                    } 
                }
                $minOrderAmount = $condition->min_order_amount;
                if ($minOrderAmount > $totalPrice) {
                    $cart->coupon_code = null;
                    $result[] = "Đơn hàng chưa đạt giá trị tối thiểu";
                }

                // Chỉ dành cho thiết bị mobile
                if((!(get_device()=="PHONE")) && (!(get_device()=="TABLET")) && (!is_null($condition->mobile_app_only))){
                    $cart->coupon_code = null;
                    $result[] = "Chỉ áp dụng cho thiết bị di động";
                };
                
                // Cho khách hàng đã đăng nhập
                if((is_null($cart->user_id)) && (!is_null($condition->for_loged_in_users))){
                    $cart->coupon_code = null;
                    $result[] = "Chỉ áp dụng cho tài khoản đã đăng nhập";
                }

                // Có sản phẩm yêu cầu trong giỏ mới được áp
                if ((!is_null($condition->applicable_product))) {
                    $product = $cartDetails->where("product_id",$condition->applicable_product)->first();
                    if (is_null($product)) {
                        $cart->coupon_code = null;
                        $result[] = "Không áp dụng được, sản phẩm yêu cầu không có trong giỏ hàng";
                    }
                   
                }
                
            }
            if (($coupon->coupon_date_end < $now) or ($coupon->coupon_date_start > $now)){// Coupon hết hạn/chưa tới thời gian bắt đầu
                $cart->coupon_code = null;
            }
            $cart->save();
            if ($coupon->coupon_date_end < $now) {
                $result[] = "Coupon đã hết hạn";
            }
            if ($coupon->coupon_date_start > $now) {
                $result[] = "Coupon chưa tới thời gian sử dụng";
            }
        }

        // VOUCHER
        if ((!is_null($cart->voucher_code)) && ($option != 1)) {
            $voucherCode = $cart->voucher_code;
            $voucher = Voucher::find($voucherCode);
            $now = Carbon::now();

            // Condition
            $result=[];
            $condition = $voucher->condition;
            if(!is_null($condition)){
                // Giá trị đơn hàng tối thiểu
                foreach ($cart->info as $info) {
                    if ($info["code"]=="sub_total") {
                        // Tổng giỏ hàng
                        $totalPrice = $info["value"];
                    } 
                }
                $minOrderAmount = $condition->min_order_amount;
                if ($minOrderAmount > $totalPrice) {
                    $cart->voucher_code = null;
                    $result[] = "Đơn hàng chưa đạt giá trị tối thiểu";
                }

                // Chỉ dành cho thiết bị mobile
                if((!(get_device()=="PHONE")) && (!(get_device()=="TABLET")) && (!is_null($condition->mobile_app_only))){
                    $cart->voucher_code = null;
                    $result[] = "Chỉ áp dụng cho thiết bị di động";
                }
                
                // Cho khách hàng đã đăng nhập
                if((is_null($cart->user_id)) && (!is_null($condition->for_loged_in_users))){
                    $cart->voucher_code = null;
                    $result[] = "Chỉ áp dụng cho tài khoản đã đăng nhập";
                }

                // Có sản phẩm yêu cầu trong giỏ mới được áp
                if ((!is_null($condition->applicable_product))) {
                    $product = $cartDetails->where("product_id",$condition->applicable_product)->first();
                    if (is_null($product)) {
                        $cart->voucher_code = null;
                        $result[] = "Không áp dụng được, sản phẩm yêu cầu không có trong giỏ hàng";
                    }
                   
                }

                // Chỉ đơn hàng đầu tiên
                if (!is_null($condition->first_order_only)){
                    $userId = SERVICE::getCurrentUserId();
                    $checkFirstOrder = Order::where('user_id',$userId)->whereNull('deleted_at')->exists();
                    if ($checkFirstOrder) {
                        $result[] = "Chỉ áp dụng được cho đơn hàng đầu tiên";
                    }
                    // $checkFirstOrder = Order::where('user_id',$userId)->exists(); //Van chay duoc
                }
                
            }

            if (($voucher->voucher_date_end < $now) or ($voucher->voucher_date_start > $now)){// Voucher hết hạn/chưa tới thời gian bắt đầu
                $cart->voucher_code = null;
            }

            $cart->save();

            if ($voucher->voucher_date_end < $now) {
                $result[] = "Voucher đã hết hạn";
            }
            if ($voucher->voucher_date_start > $now) {
                $result[] = "Voucher chưa tới thời gian sử dụng";
            }
        }
        return $result;
    }
    public function checkSessionOrUser($userID = null,$session = null){
        // Kiểm tra userID hay session có thuộc giỏ hàng truyền vào không
        // $userID = SERVICE::getCurrentUserId();
        // "guest_session" =>  [Rule::requiredIf(!$userID),"exists:sessions,session_id"],
        $cart = $this->cart;
        $checkLogin = !is_null($userID);
        if ($checkLogin) {
            //user
            $check = $cart->user_id;
            if(!($check == $userID)){
                return "Không đúng thông tin";
            };
        }else{
            $guestID = Session::where("session_id", $session)->value("id");
            $check = $cart->guest_id;
            if (!($check == $guestID)) {
                return "Không đúng thông tin";
            }
        }
    }
}
<?php

namespace App\Http\Controllers\V1\Normal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpParser\Node\Expr\Cast;

class Cart extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'carts';

    protected $primaryKey = "id";

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'info' => 'json'
    ];

    protected $fillable = [
        "id",
        "user_id",
        "guest_id",
        "name",
        "phone",
        "payment_method",
        "payment_status",
        "coupon_code",
        "voucher_code",
        "street_no",
        "ward_id",
        "ward_name",
        "district_id",
        "district_name",
        "city_id",
        "city_name",
        "address",
        "free_item",
        "info",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function cartDetails() {
        return $this->hasMany(CartDetail::class, 'cart_id', 'id');
    }
    public function deleteCartDetails() {
        $this->cartDetails()->delete();
    }
    public function session(){
        return $this->hasOne(Session::class, 'id','guest_id');
    }
    public function coupon(){
        return $this->hasOne(Coupon::class,'coupon_code','coupon_code');
    }
    public function voucher(){
        return $this->hasOne(Voucher::class,'voucher_code','voucher_code');
    }

    // Giá tạm tính
    // giá tối đa max_discoun -> %
    public function scopeSetInfoCart($query, $cartID){
        $total = CartDetail::where('cart_id',$cartID)->sum("total");
        $subTotal = $total;
        $data = [
            [
                "code" => "sub_total",
                "title" => "Tổng tiền hàng",
                "text" => number_format($subTotal, 0, ',', '.') . ' đ',
                "value" => $subTotal
            ]
        ];

        $cart = $query->find($cartID);
        if ((!is_null($cart->coupon)) && (is_null($cart->voucher))) {
            if($cart->coupon->coupon_type == "F"){
                $total -= $cart->coupon->coupon_value;
            }
            // % 1 - 100
            if($cart->coupon->coupon_type == "T"){
                $maxDiscoun = $cart->coupon->conditions?->max_discoun;
                $discoun = (($cart->coupon->coupon_value/100)*$total);
                if (is_null($maxDiscoun)) {
                    $total -= $discoun;
                }else{
                    $total -= ($maxDiscoun > $discoun) ? $discoun : $maxDiscoun;
                }
            }
            $data[] = [
                "code" => "coupon",
                "title" => $cart->coupon->title,
                "text" => number_format($subTotal - $total, 0, ',', '.') . ' đ',
                "value" => $subTotal - $total
            ];
        } 
        if ((!is_null($cart->voucher)) && (is_null($cart->coupon))) {
            if($cart->voucher->voucher_type == "F"){
                $total -= $cart->voucher->voucher_value;
            }
            // $ 1 - 100
            if($cart->voucher->voucher_type == "T"){
                $maxDiscoun = $cart->voucher->condition?->max_discoun;
                $discoun = (($cart->voucher->voucher_value/100)*$total);
                if (is_null($maxDiscoun)) {
                    $total -= $discoun;
                }else{
                    $total -= ($maxDiscoun > $discoun) ? $discoun : $maxDiscoun;
                }
            }
            $data[] = [
                "code" => "voucher",
                "title" => $cart->voucher->title,
                "text" => number_format($subTotal - $total, 0, ',', '.') . ' đ',
                "value" => $subTotal - $total
            ];
        } 
        $data[] = [
            "code" => "total",
            "title" => "Tổng thanh toán",
            "text" => number_format($total, 0, ',', '.') . ' đ',
            "value" => $total
        ];
        $cart->info = $data;
        $cart->save();
        return;
    }

}
<?php

namespace App\Http\Controllers\V1\Normal\Resources\Cart;

use App\Http\Controllers\V1\Auth\Models\Price;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class CartResource extends BaseResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request)
    {
        try {
            return [
                'id'   => $this->id,
                'user_id' => $this->user_id,
                'guest_id' => $this->guest_id, // ép kiểu số
                'name' => $this->name,
                'phone' => $this->phone,
                'payment_method' => $this->payment_method,
                'payment_status' => $this->payment_status,
                'coupon_code' => $this->coupon_code,
                'voucher_code' => $this->voucher_code,
                'street_no' => $this->street_no,
                'ward_id' => $this->ward_id,
                'district_id' => $this->district_id,// + name
                'city_id' => $this->city_id,
                'address' => $this->address,
                'free_item' => $this->free_item,// json
                'info' => $this->info,
                'cart_details'   => $this->cartDetails->map(function ($item) {
                    return [
                        'id'        => $item->id,
                        'cart_id' => $item->cart_id,
                        'product_id'     => $item->product_id, // 
                        'product_name' => $item->product?->product_name, //?-> >7.4 Arr:get, array_get, object_get
                        'slug' => $item->product?->slug,
                        'sku' => $item->product?->sku,
                        'category_id' => $item->product?->category_id,
                        'category_name' => $item->product?->first()->category->name,
                        'short_description' => $item->product?->short_description,
                        'thumpnail_url' => $item->product?->thumpnail_url,
                        'quantity'      => $item->quantity,
                        'price'      => Price::getProductPrice(Product::find($item->product_id)),
                        'option'      => $item->option,
                        'total'      => $item->total,
                    ];
                }),
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

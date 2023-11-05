<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\AddressBook;
use App\Http\Controllers\V1\Auth\Resources\AddressBook\AddressBookCollection;
use App\Http\Controllers\V1\Normal\Models\Cart;
use App\SERVICE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressBookController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new AddressBook();
    }
    
    // Get
    public function getAddressAllBooks(Request $request)
    {
        $price = $this->model->search($request->all());
        return new AddressBookCollection($price);
    }

    public function getAddressBooks(Request $request)
    {
        $input = $request;
        $userID = SERVICE::getCurrentUserId();
        $input['user_id'] = $userID;
        $price = $this->model->search($input->all());
        return new AddressBookCollection($price);
    }


    public function createAddressBook(Request $request)
    {
        $input = $request;
        $userID = SERVICE::getCurrentUserId();
        $input['user_id'] = $userID;
        $this->validate($request,[
            // "user_id" => "required|exists:users,id",
            "full_name" => "required",
            "phone" => "required|max:10",
            "ward_id" => "required",
            "ward_name" => "required",
            "district_id" => "required",
            "district_name" => "required",
            "city_id" => "required",
            "city_name" => "required",
            "full_address" => "required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "max" => "Trường :attribute tối đa :max kí tự",
            // "numeric" => "Trường :attribute phải là số",
            "exists" => "ID sản phẩm không tồn tại"
        ]);

        $result = $this->model->create($input->all());
        // if ($result) {
        //     return $this->responseError($result);
        // }
        return $this->responseSuccess("Thêm địa chỉ thành công");   

    }

    public function updateAddressBook(Request $request, $id)
    {
        // $input = $request;
        // $input["user_id"] = 
        $this->validate($request,[
            "user_id" => "sometimes|required|exists:users,id",
            "full_name" => "sometimes|required",
            "phone" => "sometimes|required|max:10",
            "ward_id" => "sometimes|required",
            "ward_name" => "sometimes|required",
            "district_id" => "sometimes|required",
            "district_name" => "sometimes|required",
            "city_id" => "sometimes|required",
            "city_name" => "sometimes|required",
            "full_address" => "sometimes|required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "max" => "Trường :attribute tối đa :max kí tự",
            // "numeric" => "Trường :attribute phải là số",
            "exists" => "ID sản phẩm không tồn tại"
        ]);

        $addressBook = $this->model->find($id);
        if (empty($addressBook)) {
            return $this->responseError("Không tìm thấy địa chỉ");
        }
        if (empty($request->all())) {
            return $this->responseError("Không có thay đổi");
        }
        try {
            $result = $addressBook->update($request->all());
            if ($result) {
                return $this->responseSuccess("Cập nhật thành công");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }

    }

    // Xác nhận địa chỉ cho giỏ hàng
    public function confirmAddress(Request $request){
        $userID = SERVICE::getCurrentUserId();
        $cart = Cart::where("user_id",$userID)->first();
        $this->validate($request,[
            // "user_id" => "required|exists:users,id",
            "full_name" => "required",
            "phone" => "required|max:10",
            "ward_id" => "required",
            "ward_name" => "required",
            "district_id" => "required",
            "district_name" => "required",
            "city_id" => "required",
            "city_name" => "required",
            "full_address" => "required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "max" => "Trường :attribute tối đa :max kí tự",
            // "numeric" => "Trường :attribute phải là số",
            "exists" => "ID sản phẩm không tồn tại"
        ]);
        try {
            $cart->name = $request->full_name;
            $cart->phone = $request->phone;
            $cart->ward_id = $request->ward_id;
            $cart->ward_name = $request->ward_name;
            $cart->district_id = $request->district_id;
            $cart->district_name = $request->district_name;
            $cart->city_id = $request->city_id;
            $cart->city_name = $request->city_name;
            $cart->address = $request->full_address;
            $cart->save();
            return $this->responseSuccess("Chọn địa chỉ thành công");
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }
    }
    // public function getPriceById($id)
    // {
    //     $priceID = PriceDetail::find($id)->price_id;
    //     $price = Price::find($priceID);
    //     if (empty($price)) {
    //         return $this->responseError("Không tìm thấy price với ID: $id");
    //     }
    //     return new PriceResource($price);
    // }



    // public function removePrice($id)
    // {
    //     $price = Price::find($id);
    //     if (empty($price)) {
    //         return $this->responseError("Không tìm thấy price với ID: $id");
    //     }
    //     $price->priceDetails()->delete();
    //     $price->delete();
    //     return $this->responseSuccess("Xóa price thành công");
    // }

}

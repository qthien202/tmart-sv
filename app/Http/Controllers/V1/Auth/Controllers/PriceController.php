<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Price;
use App\Http\Controllers\V1\Auth\Models\PriceDetail;
use App\Http\Controllers\V1\Auth\Resources\Price\PriceCollection;
use App\Http\Controllers\V1\Auth\Resources\Price\PriceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Price();
    }
    
    // Get prices table
    public function getPrices(Request $request)
    {
        $price = $this->model->search($request->all());
        return new PriceCollection($price);
    }


    public function createPrice(Request $request)
    {
        $this->validate($request,[
            "product_id" => "required|exists:products,id",
            "price" => "required|numeric",
            "currency" => "required",
            "effective_date" => "required",
            "expire_date" => "required"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "numeric" => "Trường :attribute phải là số",
            "product_id.exists" => "ID sản phẩm không tồn tại"
        ]);

        $result = Price::createPrice($this->model,$request->all());
        if ($result) {
            return $this->responseError($result);
        }
        return $this->responseSuccess("Thêm giá thành công");   

    }

    public function getPriceById($id)
    {
        $priceID = PriceDetail::find($id)->price_id;
        $price = Price::find($priceID);
        if (empty($price)) {
            return $this->responseError("Không tìm thấy price với ID: $id");
        }
        return new PriceResource($price);
    }

    public function updatePrice(Request $request, $id)
    {
        $this->validate($request,[
            "product_id" => "sometimes|required|exists:products,id",
            "price" => "sometimes|required|numeric",
            "currency" => "sometimes|required",
            "effective_date" => "sometimes|required",
            "expire_date" => "sometimes|required"
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "numeric" => "Trường :attribute phải là số",
            "product_id.exists" => "ID sản phẩm không tồn tại"
        ]);

        $priceID = PriceDetail::find($id)->price_id;
        $price = Price::find($priceID);
        if (empty($price)) {
            return $this->responseError("Không tìm thấy price với ID: $id");
        }

        $result = Price::updatePrice($price,$request->all());
        if($result){
            return $this->responseError($result);
        };
        return $this->responseSuccess("Cập nhật thành công");
    }

    public function removePrice($id)
    {
        $price = Price::find($id);
        if (empty($price)) {
            return $this->responseError("Không tìm thấy price với ID: $id");
        }
        $price->priceDetails()->delete();
        $price->delete();
        return $this->responseSuccess("Xóa price thành công");
    }

}

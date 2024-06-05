<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Category;
use App\Http\Controllers\V1\Auth\Models\Favorite;
use App\Http\Controllers\V1\Auth\Models\Price;
use Illuminate\Http\Request;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\Http\Controllers\V1\Auth\Resources\Favorite\FavoriteCollection;
use App\Http\Controllers\V1\Auth\Resources\Product\ProductCollection;
use App\Http\Controllers\V1\Auth\Resources\Product\ProductResource;
use App\SERVICE;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Product();
    }

    // Lấy danh sách các sản phẩm
    public function getProducts(Request $request)
    {
        // return $this->model->find(235)->category()->get();
        $product = $this->model->search($request->all());
        return new ProductCollection($product);
    }
    
    public function getProductsCate(Request $request)
    {
        $category_name = Category::select("name","id")->get();
        $data=[];

        for ($i=0; $i < count($category_name); $i++) { 
            $dataRequest = ["category_id" => $category_name[$i]->id];

            $data[] = [
                $category_name[$i]->name => $this->getProducts(new Request($dataRequest))
            ];
        }
        return $data;
        // $product = $this->model->search($request->all());
        // return new ProductCollection($product);

    }
    // Lưu dữ liệu tạo mới vào cơ sở dữ liệu
    public function createProduct(Request $request)
    {
        $this->validate($request, [
            "code" => "required|sometimes|string|unique:products",// Code sản phẩm đã tồn tại
            "product_name" => "required|string",
            "price" => "required|numeric",

            "discount_price" => "required|sometimes", #Chưa làm: Ngày bắt buộc khi có discount_price
            "effective_date" =>"required|sometimes",
            "expire_date" =>"required|sometimes",


            "slug" =>  "sometimes|required|string",
            "sku" => "sometimes|required|string",
            "short_description" => "sometimes|required|string",
            "description" => "sometimes|required|string",
            "category_id" => "required|integer|exists:categories,id",
            "stock_quantity" => "sometimes!required|integer",
            "manufacturer_id" => "sometimes|required|integer|exists:manufacturers,id",
            "unit_id" => "sometimes|required|integer|exists:units,id",
            "packaging_id" => "sometimes|required|integer|exists:packaging_units,id",
            "thumpnail_url" => "sometimes|required|string",
            "gallery_images_url" => "sometimes|required",
            "views" => "sometimes|required|integer",
            "tags" => "sometimes|required|string",
            "meta_title" => "sometimes|required|string",
            "meta_description" => "sometimes|required|string",
            "meta_keywork" => "sometimes|required|string",
            "meta_robot" => "sometimes|required|string",
            "type" => "sometimes|required|string",
            "is_featured" => "sometimes|required|integer",
            "related_ids" => "sometimes|required|json"
        ], [
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là chuỗi",
            "exists" => "Trường :attribute không tồn tại",
            "integer" => "Trường :attribute phải là số nguyên",
            "json" => "Trường :attribute phải là json",
            "code.unique" => "Mã sản phẩm đã tồn tại"
        ]);
        // $productExists = Product::where("code",$request->code)->exists();
        // if ($productExists) {
        //     return $this->responseError("Code sản phẩm đã tồn tại");
        // }
        $result = $this->model->create($request->all());
        if (!empty($request->discount_price)&&!empty($request->effective_date)&&!empty($request->expire_date)) {

            $data['effective_date'] = $request->effective_date;
            $data['expire_date'] = $request->expire_date;
            $data['currency'] = 'VND';
            $data['product_id'] = $result->id;
            $data['price'] = $request->discount_price;
            
            Price::createPrice(new Price(),$data);

        }
        return $this->responseSuccess("Thêm sản phẩm thành công");
    }

    // Hiển thị thông tin chi tiết của một sản phẩm
    public function getProductById($id)
    {
        $product = Product::find($id);
        $product->views = $product->views+1;
        $product->save();
        if (empty($product)) {
            return $this->responseError("Không tìm thấy sản phẩm với ID: $id");
        }
        return new ProductResource($product);
    }

    public function getProductByCategoryId($id){
        $product = Product::where("category_id",$id)->get();
        return new ProductCollection($product);
    }

    public function addToFavorite(Request $request){
        $this->validate($request,[
            "product_id" => "required|integer|exists:products,id",
        ]);
        $userId = SERVICE::getCurrentUserId();
        try {
            $check = Favorite::where("user_id",$userId)->where("product_id",$request->product_id)->exists();
            if ($check) {
                return $this->responseSuccess("Đã có sản phẩm trong yêu thích");
            }
            DB::beginTransaction();
            $favorite = new Favorite();
            $favorite->user_id = $userId;
            $favorite->product_id = $request->product_id;
            $favorite->save();
            DB::commit();
            return $this->responseSuccess("Đã thêm sản phẩm vào yêu thích");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError("Thêm thất bại: Error: ".$th->getMessage());
        }

    }

    public function removeProductFromFavorites(Request $request){
        $this->validate($request,[
            "product_id" => "required|integer|exists:products,id",
        ]);
        $userId = SERVICE::getCurrentUserId();
        try {
            DB::beginTransaction();
            $favorite = Favorite::where('user_id',$userId)->where("product_id",$request->product_id)->first();
            if (empty($favorite)) {
                return $this->responseError("Không tìm thấy!");
            }
            $result = $favorite->delete();
            DB::commit();
            return $this->responseSuccess("Đã xóa sản phẩm yêu thích");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError("Xóa thất bại: Error: ".$th->getMessage());
        }
    }

    public function getFavoriteProducts(Request $request){
        $userId = SERVICE::getCurrentUserId();
        $input = $request->all();
        $input['user_id'] = $userId;
        $favorite = Favorite::search($input);
        return new FavoriteCollection($favorite);
    }

    public function updateProduct(Request $request, $id)
    {
        $this->validate($request, [
            "code" => "sometimes|required|string|unique:products",// Code sản phẩm đã tồn tại
            "product_name" => "sometimes|required|string",
            "price" => "sometimes|required|numeric",
            "slug" =>  "sometimes|required|string",
            "sku" => "sometimes|required|string",
            "short_description" => "sometimes|required|string",
            "description" => "sometimes|required|string",
            "category_id" => "sometimes|required|integer|exists:categories,id",
            "stock_quantity" => "sometimes|required|integer",
            "manufacturer_id" => "sometimes|required|integer|exists:manufacturers,id",
            "unit_id" => "sometimes|required|integer|exists:units,id",
            "packaging_id" => "sometimes|required|integer|exists:packaging_units,id",
            "thumpnail_url" => "sometimes|required|string",
            "gallery_images_url" => "sometimes|required|string",
            "views" => "sometimes|required|integer",
            "tags" => "sometimes|required|string",
            "meta_title" => "sometimes|required|string",
            "meta_description" => "sometimes|required|string",
            "meta_keywork" => "sometimes|required|string",
            "meta_robot" => "sometimes|required|string",
            "type" => "sometimes|required|string",
            "is_featured" => "sometimes|required|integer",
            "related_ids" => "sometimes|required|json"
        ], [
            "required" => "Trường :attribute là bắt buộc",
            "string" => "Trường :attribute phải là chuỗi",
            "exists" => "Trường :attribute không tồn tại",
            "integer" => "Trường :attribute phải là số nguyên",
            "json" => "Trường :attribute phải là json",
            "code.unique" => "Mã sản phẩm đã tồn tại"
        ]);
        $product = Product::find($id);
        if (empty($product)) {
            return $this->responseError("Không tìm thấy sản phẩm với ID: $id");
        }
        try {
            $result = $product->update($request->all());
            if ($result) {
                return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
            }
        } catch (\Throwable $th) {
            return $this->responseError($th->getMessage());
        }

    }

    public function removeProduct($id)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return $this->responseError("Không tìm thấy sản phẩm với ID: $id");
        }
        $product->delete();
        return $this->responseSuccess("Xóa sản phẩm thành công");
    }
}



<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Http\Controllers\V1\Auth\Models\Comment;
use App\Http\Controllers\V1\Auth\Models\Product;
use App\Http\Controllers\V1\Auth\Resources\Comment\CommentCollection;
use App\SERVICE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Comment();
    }
    
    public function getComments(Request $request)
    {
        $comment = $this->model->search($request->all());
        return new CommentCollection($comment);
    }

    public function addComment(Request $request){
        $this->validate($request,[
            "product_id" => "required|integer",
            "rating" => "required",
            "text" => "required",
        ],[
            "required" => "Trường :attribute là bắt buộc",
            "integer" => "Trường :attribute là số nguyên"
        ]);
        $userId = SERVICE::getCurrentUserId();
        $productId = $request->product_id;
        $test = empty($request->test)?true:false; // chế độ test thêm comment
        // 1 User chỉ bình luận 1 sản phẩm 1 lần
        $check = Comment::where("user_id",$userId)->where("product_id",$productId)->exists();
        if ($check and $test) {
            return $this->responseError("User này đã bình luận sản phẩm này rồi!");
        }
        
        try {
            $product = Product::find($productId);
            
            DB::beginTransaction();
            if (empty($product->average_rating)) {
                // Thêm thống kê sao vào
                $rating_distribution = [
                    "s5"=>0,
                    "s4"=>0,
                    "s3"=>0,
                    "s2"=>0,
                    "s1"=>0
                ];
                $rating_distribution["s".$request->rating] = 1;
                $product->rating_distribution = json_encode($rating_distribution);
                // Thêm đánh giá vào
                $product->average_rating = $request->rating;
            }else{
                $rating_distribution = json_decode($product->rating_distribution, true);
                $rating_distribution["s".$request->rating] = $rating_distribution["s".$request->rating] + 1;
                $product->rating_distribution = json_encode($rating_distribution);
                $product->average_rating = ($product->average_rating * $product->num_reviews + $request->rating)/($product->num_reviews+1);

            }

            $product->num_reviews = $product->num_reviews + 1;
            $product->save();

            // $product
            $request["user_id"] = $userId;
            $this->model->create($request->all());
            DB::commit();
            return $this->responseSuccess("Thêm comment thành công");
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError("Thêm thất bại: Error: ".$th->getMessage());
        }
        
    }

    public function getImgsFromProductId(Request $request){
        $this->validate($request,[
            'product_id'=>'required|exists:products,id',
        ]);
        $comment = Comment::where('product_id',$request->product_id)->select('id','image_url')->get();
        return $this->responseSuccess(null, $comment);
    }
    // public function createUnit(Request $request)
    // {
    //     $this->validate($request,[
    //         "code" => "required",
    //         "name" => "required"
    //     ],[
    //         "required" => "Trường :attribute là bắt buộc"
    //     ]);
    //     $this->model->create($request->all());
    //     return $this->responseSuccess("Thêm unit thành công");
    // }

    // public function getUnitById($id)
    // {
    //     $unit = Unit::find($id);
    //     if (empty($unit)) {
    //         return $this->responseError("Không tìm thấy unit với ID: $id");
    //     }
    //     return new UnitResource($unit);
    // }

    // public function updateUnit(Request $request, $id)
    // {
    //     $this->validate($request,[
    //         "code" => "required",
    //         "name" => "required"
    //     ],[
    //         "required" => "Trường :attribute là bắt buộc"
    //     ]);
    //     $unit = Unit::find($id);
    //     if (empty($unit)) {
    //         return $this->responseError("Không tìm thấy unit với ID: $id");
    //     }
    //     try {
    //         $result = $unit->update($request->all());
    //         if ($result) {
    //             return $this->responseSuccess("Không xảy ra lỗi trong quá trình cập nhật");
    //         }
    //     } catch (\Throwable $th) {
    //         return $this->responseError($th->getMessage());
    //     }
    // }

    // public function removeUnit($id)
    // {
    //     $unit = Unit::find($id);
    //     if (empty($unit)) {
    //         return $this->responseError("Không tìm thấy unit với ID: $id");
    //     }
    //     $unit->delete();
    //     return $this->responseSuccess("Xóa unit thành công");
    // }
}

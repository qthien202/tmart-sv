<?php

namespace App\Http\Controllers\V1\Auth\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Price extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    protected $table = 'prices';

    protected $primaryKey = "id";

    // protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "effective_date",
        "expire_date"
    ];

    // Search
    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['id'])) {
            $query->where('id', $params['id']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    // Relationship
    public function priceDetails(){
        return $this->hasOne(PriceDetail::class, "price_id", "id");
    }

    // Get price from product
    public static function getProductPrice($product){
        // Ngày bắt đầu

        // Hết hạn -> xóa => ưu tiên bảng giá thêm sau
        $deletePrice = Price::whereDate("expire_date","<",Carbon::now());
        PriceDetail::where("price_id",$deletePrice->value("id"))->delete();
        $deletePrice->delete();


        // Price::whereDate("effective_date",">=",Carbon::now());
        // Lấy bảng giá được thêm sau ra
        $price = PriceDetail::select("*")->whereNull("deleted_at")->where("product_id",$product->id)->orderBy('created_at', 'desc')->first();
        if (!empty($price)) {
            if ($price->prices()->value("effective_date")<=Carbon::now()) {
                return $price->price;
            }   
        }
        return $product->price;


    }

    // Create Price Table
    public static function createPrice($price, $data){
        // Add to db
        DB::beginTransaction();
        try {
            $priceDetail = new PriceDetail();
    
            $price->effective_date = $data["effective_date"];
            $price->expire_date = $data["expire_date"];
            $price->save();
    
            $priceDetail->product_id = $data["product_id"];
            $priceDetail->price = $data["price"];
            $priceDetail->price_id = $price->id;
            $priceDetail->currency = $data["currency"];
            $priceDetail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return $th->getMessage();
        }
    }

    // Update Price
    public static function updatePrice($price,$data){
        
        $priceDetail = $price->priceDetails()->first();

        // Note: Cách 2. NULL -> Dữ liệu cũ?
        DB::beginTransaction();
        try {
            foreach ($data as $key => $value) {
                if (in_array($key,["effective_date","expire_date"])) {
                    $price->$key = $value;
                }
                if (in_array($key,["product_id","price","currency"])) {
                    $priceDetail->$key = $value;
                }
            }
            $price->save();
            $priceDetail->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
        }
    }
}
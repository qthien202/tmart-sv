<?php

namespace App\Http\Controllers\V1\Normal\Models;

use App\Http\Controllers\V1\Auth\Models\Price;
use App\Http\Controllers\V1\Auth\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartDetail extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'cart_details';

    protected $primaryKey = "id";

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "cart_id",
        "product_id",
        "product_name",
        "quantity",
        "price",
        "option",
        "total",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function cart(){
        return $this->belongsTo(Cart::class, "cart_id");
    }

    public function product(){
        return $this->belongsTo(Product::class, "product_id");
    }

    public function scopeUpdateTotal($query, $id){
        // ID = Cart Detail ID
        $cartDetail = $query->find($id);
        $product = Product::find($cartDetail->product_id);

        $price = Price::getProductPrice($product);

        $quantity = $cartDetail->quantity;
        $cartDetail->price = $price;
        $cartDetail->total = $price * $quantity;
        $cartDetail->save();
        return;
    }

    public function scopeUpdateDetail(){

    }
}
<?php

namespace App\Http\Controllers\V1\Auth\Models;

use App\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Product extends Model
{
    // public $timestamps = false;
    use SoftDeletes;

    protected $table = 'products';

    protected $primaryKey = "id";

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        "id",
        "code",
        "product_name",
        "price",
        "slug",
        "sku",
        "description",
        "detail",
        "category_id",
        "stock_quantity",
        "manufacturer_id",
        "unit_id",
        "packaging_id",
        "thumpnail_url",
        "gallery_images_url",
        "views",
        "tags",
        "meta_title",
        "meta_description",
        "meta_keywork",
        "meta_robot",
        "type",
        "is_featured",
        "related_ids"
    ];

    protected $casts = [
        'gallery_images_url' => 'json'
    ];

    public function scopeSearch($query, $params){
        $query->select('*');
        if (isset($params['code'])) {
            $query->where('code', $params['code']);
        }
        // if (isset($params['category_name'])) {
        //     $cateID = Category::where("name",$params['category_name'])->value("id");
        //     $query->where('category_id', $cateID);
        // }
        if (isset($params['product_name'])) {
            $cateID = Category::where("name",$params['product_name'])->value("id");
            if (empty($cateID)) {
                $query->where('product_name', 'like', '%' . $params['product_name'] . '%');
            }
            else{
                $query->where('category_id', $cateID);
            }
        }
        if (isset($params['price'])){
            $query->whereHas('priceDetails', function ($subquery) use ($params){
                if (in_array("2",$params['price'])) {
                    $subquery->where('price', '>=', 0)->where('price','<',2000000);
                }
                if (in_array("2-4",$params['price'])) {
                    $subquery->where('price', '>=', 2000000)->where('price','<=',4000000);
                }
                if (in_array("4-7",$params['price'])) {
                    $subquery->where('price', '>=', 4000000)->where('price','<=',7000000);
                }
                if (in_array("7-13",$params['price'])) {
                    $subquery->where('price', '>=', 7000000)->where('price','<=',13000000);
                }
                if (in_array("13-20",$params['price'])) {
                    $subquery->where('price', '>=', 13000000)->where('price','<=',20000000);
                }
                if (in_array("20",$params['price'])) {
                    $subquery->where('price', '>=', 20000000);
                }
            });
        }
        // if (isset($params['price'])) {//array [min,max]
        //     $query->whereBetween('price', $params['price']);
        // }
        // if (isset($params['price'])) {//array [1,2,3,6]
        //     // $query->whereHas('priceDetails', function (Builder $query){
        //     //     $query->where('price', $params['price']);
        //     // });
        //     $query->where(function ($query) use ($params){
        //         // $query->where("price",">=","0");
                 

        //         // Dưới 2 triệu
        //         if (in_array("2",$params['price'])) {
        //             // $query->orWhereBetween('price', [0,2000000]);
        //             // $query->whereHas('prices', function ($q) use ($minPrice, $maxPrice, $now) {
        //             //     $q->whereBetween('price', [$minPrice, $maxPrice])
        //             //       ->where('expiry_date', '>', $now);
        //             // });
        //             dd(Price::searchProductPrice(0,2000000));
        //         }
        //         // 2 - 4 triệu
        //         if (in_array("2-4",$params['price'])) {
        //             $query->orWhereBetween('price', [2000000,4000000]);
        //         }
        //         // 4 - 7 triệu
        //         if (in_array("4-7",$params['price'])) {
        //             $query->orWhereBetween('price', [4000000,7000000]);
        //         }
        //         // 7 - 13 triệu
        //         if (in_array("7-13",$params['price'])) {
        //             $query->orWhereBetween('price', [7000000,13000000]);
        //         }
        //         // 13 - 20 triệu
        //         if (in_array("13-20",$params['price'])) {
        //             $query->orWhereBetween('price', [13000000,20000000]);
        //         }
        //         // trên 20 triệu
        //         if (in_array("20",$params['price'])) {
        //             $query->orWhere('price',">", 20000000);
        //         }
        //     });
            
           
            
        // }
        if (isset($params['slug'])) {
            $query->where('slug', $params['slug']);
        }
        if (isset($params['sku'])) {
            $query->where('sku', $params['sku']);
        }
        if (isset($params['description'])) {
            $query->where('description', $params['description']);
        }
        if (isset($params['category_id'])) {//array
            $query->whereIn('category_id', $params['category_id']);
        }
        if (isset($params['stock_quantity'])) {
            $query->where('stock_quantity', $params['stock_quantity']);
        }
        if (isset($params['manufacturer_id'])) {//array
            $query->whereIn('manufacturer_id', $params['manufacturer_id']);
        }
        if (isset($params['unit_id'])) {
            $query->where('unit_id', $params['unit_id']);
        }
        if (isset($params['packaging_id'])) {
            $query->where('packaging_id', $params['packaging_id']);
        }
        if (isset($params['thumpnail_url'])) {
            $query->where('thumpnail_url', $params['thumpnail_url']);
        }
        if (isset($params['gallery_images_url'])) {
            $query->where('gallery_images_url', $params['gallery_images_url']);
        }
        if (isset($params['views'])) {
            $query->where('views', $params['views']);
        }
        if (isset($params['tags'])) {
            $query->where('tags', $params['tags']);
        }
        if (isset($params['meta_title'])) {
            $query->where('meta_title', $params['meta_title']);
        }
        if (isset($params['meta_desctiption'])) {
            $query->where('meta_desctiption', $params['meta_desctiption']);
        }
        if (isset($params['meta_keywork'])) {
            $query->where('meta_keywork', $params['meta_keywork']);
        }
        if (isset($params['meta_robot'])) {
            $query->where('meta_robot', $params['meta_robot']);
        }
        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }
        if (isset($params['is_featured'])) {
            $query->where('is_featured', $params['is_featured']);
        }
        if (isset($params['related_ids'])) {
            $query->where('related_ids', $params['related_ids']);
        }
        return $query->paginate(Arr::get($params,'perPage', 10));
    }

    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }
    public function manufacturer(){
        return $this->belongsTo(Manufacturer::class,'manufacturer_id');
    }

    public function priceDetails(){
        return $this->hasMany(PriceDetail::class, 'product_id', 'id');
    }
}
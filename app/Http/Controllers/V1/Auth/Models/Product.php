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
        if (isset($params['product_name'])) {
            $query->where('product_name', 'like', '%' . $params['product_name'] . '%');
        }
        if (isset($params['price'])) {
            $query->where('price', $params['price']);
        }
        if (isset($params['slug'])) {
            $query->where('slug', $params['slug']);
        }
        if (isset($params['sku'])) {
            $query->where('sku', $params['sku']);
        }
        if (isset($params['description'])) {
            $query->where('description', $params['description']);
        }
        if (isset($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }
        if (isset($params['stock_quantity'])) {
            $query->where('stock_quantity', $params['stock_quantity']);
        }
        if (isset($params['manufacturer_id'])) {
            $query->where('manufacturer_id', $params['manufacturer_id']);
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

    public function priceDetails(){
        return $this->hasMany(PriceDetail::class, 'product_id', 'id');
    }
}
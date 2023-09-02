<?php

namespace App\Http\Controllers\V1\Auth\Resources\Product;

use App\Http\Controllers\V1\Auth\Models\Price;
use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class ProductResource extends BaseResource
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
                'code' => $this->code,
                'product_name' => $this->product_name,
                'price' => Price::getProductPrice($this),
                'slug' => $this->slug,
                'sku' => $this->sku,
                'short_desctiption' => $this->short_desctiption,
                'desctiption' => $this->desctiption,
                'category_id' => $this->category_id,
                'stock_quantity' => $this->stock_quantity,
                'manufacturer_id' => $this->manufacturer_id,
                'unit_it' => $this->unit_it,
                'packaging_id' => $this->packaging_id,
                'thumpnail_url' => $this->thumpnail_url,
                'gallery_images_url' => $this->gallery_images_url,
                'views' => $this->views,
                'tags' => $this->tags,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywork' => $this->meta_keywork,
                'meta_robot' => $this->meta_robot,
                'type' => $this->type,
                'is_featured' => $this->is_featured,
                'related_ids' => $this->related_ids,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

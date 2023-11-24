<?php

namespace App\Http\Controllers\V1\Auth\Resources\Favorite;

use App\Http\Controllers\V1\Auth\Resources\Product\ProductResource;
use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class FavoriteResource extends BaseResource
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
            if (empty($this?->product)) {
                return ["message"=>"Sản phẩm đã bị xóa"];
            };
            return new ProductResource($this?->product);
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

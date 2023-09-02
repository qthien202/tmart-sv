<?php

namespace App\Http\Controllers\V1\Auth\Resources\Banner;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class BannerResource extends BaseResource
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
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
                'details'   => $this->details->map(function ($item) {
                    return [
                        'id'        => $item->id,
                        'banner_id' => $item->banner_id,
                        'image'     => $item->image,
                        'link'      => $item->link,
                        'created_at' => !empty($item->created_at) ? date('Y-m-d H:i:s', strtotime($item->created_at)) : null,
                        'updated_at' => !empty($item->updated_at) ? date('Y-m-d H:i:s', strtotime($item->updated_at)) : null
                    ];
                }),
                'created_at' => !empty($this->created_at) ? date('Y-m-d H:i:s', strtotime($this->created_at)) : null,
                'updated_at' => !empty($this->updated_at) ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : null
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

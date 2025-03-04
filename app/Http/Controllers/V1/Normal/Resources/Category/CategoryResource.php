<?php

namespace App\Http\Controllers\V1\Normal\Resources\Category;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class CategoryResource extends BaseResource
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
                'parent_id' => $this->parent_id,
                'count_child' => $this->child()->count(),
                'child' => $this->child->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'slug' => $item->slug,
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

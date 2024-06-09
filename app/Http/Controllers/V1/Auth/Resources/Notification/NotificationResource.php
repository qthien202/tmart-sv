<?php

namespace App\Http\Controllers\V1\Auth\Resources\Notification;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class NotificationResource extends BaseResource
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
                'user_id'   => $this->user_id,
                "order_id" => $this->order_id,
                "title" => $this->title,
                "content" => $this->content,
                'image_url' => $this->image_url,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

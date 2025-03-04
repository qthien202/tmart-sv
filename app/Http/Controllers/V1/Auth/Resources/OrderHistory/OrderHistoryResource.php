<?php

namespace App\Http\Controllers\V1\Auth\Resources\OrderHistory;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class OrderHistoryResource extends BaseResource
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
                'id' => $this->id,
                'user_id' => $this->user_id,
                'order_id' => $this->order_id,
                'status_code' => $this->status_Code,
                'note'=> $this->note,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $this->deleted_at,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

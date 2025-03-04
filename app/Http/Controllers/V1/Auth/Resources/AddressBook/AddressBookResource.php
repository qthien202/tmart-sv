<?php

namespace App\Http\Controllers\V1\Auth\Resources\AddressBook;

use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class AddressBookResource extends BaseResource
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
                'full_name' => $this->full_name,
                'is_default' => $this->is_default,
                'phone' => $this->phone,
                'ward_id' => $this->ward_id,
                'ward_name' => $this->ward_name,
                'district_id' => $this->district_id,
                'district_name' => $this->district_name,
                'city_id' => $this->city_id,
                'city_name' => $this->city_name,
                'street' => $this->street,
                'full_address' => $this->full_address,
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

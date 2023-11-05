<?php

namespace App\Http\Controllers\V1\Auth\Resources\AddressBook;

use App\Http\Resources\BaseCollection;

class AddressBookCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
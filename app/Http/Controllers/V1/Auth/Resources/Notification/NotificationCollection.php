<?php

namespace App\Http\Controllers\V1\Auth\Resources\Notification;

use App\Http\Resources\BaseCollection;

class NotificationCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
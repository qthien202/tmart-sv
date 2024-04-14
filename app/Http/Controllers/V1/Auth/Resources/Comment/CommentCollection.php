<?php

namespace App\Http\Controllers\V1\Auth\Resources\Comment;

use App\Http\Resources\BaseCollection;

class CommentCollection extends BaseCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
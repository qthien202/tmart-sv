<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response as HttpResponse;

class BaseCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data'           => $this->collection,
            'response'       => [
                'status' => 'success',
                'code'   => HttpResponse::HTTP_OK,
                'count'  => $this->collection->count(),
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\V1\Auth\Resources\Comment;

use App\Http\Controllers\V1\Auth\Models\Comment;
use App\Http\Resources\BaseResource;
use App\Supports\SERVICE_Error;

class CommentResource extends BaseResource
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
            $comment = Comment::where("parent_id",$this->id)->first();
            $commentRs = new CommentResource($comment);
            return [
                'id'   => $this->id,
                'product_id' => $this->product_id,
                'user_id' => $this->user_id,
                'user' => $this->user->first_name." ".$this->user->last_name,
                'user_avatar' => $this->user->avatar,
                'text' => $this->text,
                'image_url' => $this->image_url,
                'rating' => $this->rating,
                'child' => $commentRs??null,
                'parent_id' => $this->parent_id,
                'created_at' => !empty($this->created_at) ? date('Y-m-d H:i:s', strtotime($this->created_at)) : null,
                'updated_at' => !empty($this->updated_at) ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : null
            ];
        } catch (\Exception $ex) {
            $response = SERVICE_Error::handle($ex);
            throw new \Exception($response['message']);
        }
    }
}

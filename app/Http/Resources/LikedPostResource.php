<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikedPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $post = new PostResource($this->post);
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'postId' => $this->post_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'post' => $post,
            'type' => "likedpost"
        ];
    }
}

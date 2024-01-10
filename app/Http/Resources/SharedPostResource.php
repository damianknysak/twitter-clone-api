<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SharedPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $post = new PostResource($this->post);
        $user_nickname = User::where('id', $this->user_id)->first()->nickname;

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'userNickname' => $user_nickname,
            'postId' => $this->post_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'post' => $post,
            'type' => "sharedpost"
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($request->bearerToken()) {
            Auth::setUser(Auth::guard('sanctum')->user());
            $currentUserReaction = $this->likes
                ->where('comment_id', $this->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        if ($currentUserReaction) {
            $reaction = true;
        } else {
            $reaction = false;
        }
        $post = new PostResource($this->post);
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => $this->author,
            'authorId' => $this->author->id,
            'authorImage' => $this->author->profile_image,
            'authorNickname' => $this->author->nickname,
            'postId' => $this->post_id,
            'likesAmount' => $this->likes->count(),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'postRoute' => '/posts?postId[eq]=' . $this->post_id,
            'hasCurrentUserLikedThisComment' => $reaction,
            'post' => $post,
            'type' => 'comment'
        ];
    }
}

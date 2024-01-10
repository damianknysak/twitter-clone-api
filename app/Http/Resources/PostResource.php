<?php

namespace App\Http\Resources;

use App\Models\SharedPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PostResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUserReaction = null;
        $currentUserShare = null;
        if ($request->bearerToken()) {
            Auth::setUser(Auth::guard('sanctum')->user());
            $currentUserReaction = $this->likes
                ->where('post_id', $this->id)
                ->where('user_id', Auth::id())
                ->first();
            $currentUserShare = SharedPost::where("post_id", $this->id)->where("user_id", Auth::id())->first();
        }
        if ($currentUserShare) {
            $shared = true;
        } else {
            $shared = false;
        }
        if ($currentUserReaction) {
            $reaction = true;
        } else {
            $reaction = false;
        }

        $tags = $this->tags->map(function ($tag) {
            return $tag->content;
        });

        // Shares amount
        $shares_amount = SharedPost::where("post_id", $this->id)->count();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'author' => $this->author->name,
            'authorId' => $this->author->id,
            'authorImage' => $this->author->profile_image,
            'authorNickname' => $this->author->nickname,
            'authorBlurHash' => $this->author->blur_hash,
            'image' => $this->image,
            'blurHash' => $this->blur_hash,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'commentsAmount' => $this->comments->count(),
            'likesAmount' => $this->likes->count(),
            'sharesAmount' => $shares_amount,
            'hasCurrentUserLikedThisPost' => $reaction,
            'hasCurrentUserSharedThisPost' => $shared,
            'tags' => $tags,
            'type' => 'post',
        ];
    }
}

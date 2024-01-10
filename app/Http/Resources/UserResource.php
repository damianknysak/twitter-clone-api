<?php

namespace App\Http\Resources;

use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isFollowed = null;
        if ($request->bearerToken()) {
            Auth::setUser(Auth::guard('sanctum')->user());
            $isFollowed = Follower::where('user_id', $this->id)
                                ->where('follower_id', Auth::id())
                                ->first();
        }

        if ($isFollowed) {
            $currentUserIsFollowed = true;
        } else {
            $currentUserIsFollowed = false;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'email' => $this->email,
            'profileImage' => $this->profile_image,
            'blurHash' => $this->blur_hash,
            'dateOfBirth' => $this->date_of_birth,
            'description' => $this->description,
            'localization' => $this->localization,  
            'postsCount' => $this->posts->count(),
            'commentsCount' => $this->comments->count(),
            'createdAt' => $this->created_at,
            'followingsCount' => $this->followings->count(),
            'followersCount' => $this->followers->count(),
            'isFollowedByCurrentUser' => $currentUserIsFollowed,
        ];
    }
}

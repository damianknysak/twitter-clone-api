<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Http\Resources\UserResource;

class FollowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if user_id or follower_id is needed 
        if (str_contains($request->url(), 'following')) {
            $user = new UserResource(User::where('id', $this->user_id)->first());
        } else {
            $user = new UserResource(User::where('id', $this->follower_id)->first());
        }
        
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'followerId' => $this->follower_id,
            'user' => $user,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

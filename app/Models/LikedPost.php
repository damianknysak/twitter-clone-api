<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="LikedPost",
 *      title="LikedPost",
 *      description="LikedPost model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the liked post"),
 *      @OA\Property(property="user_id", type="integer", format="int64", description="ID of the user who liked the post"),
 *      @OA\Property(property="post_id", type="integer", format="int64", description="ID of the liked post"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="user", type="object", description="The user who liked the post", ref="#/components/schemas/User"),
 *      @OA\Property(property="post", type="object", description="The liked post", ref="#/components/schemas/Post"),
 * )
 */
class LikedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id'
    ];

    /**
     * Get the post that was liked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who liked the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

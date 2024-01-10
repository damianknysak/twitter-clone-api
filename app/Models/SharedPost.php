<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="SharedPost",
 *      title="SharedPost",
 *      description="SharedPost model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the shared post"),
 *      @OA\Property(property="user_id", type="integer", format="int64", description="ID of the user who shared the post"),
 *      @OA\Property(property="post_id", type="integer", format="int64", description="ID of the shared post"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="user", type="object", description="The user who shared the post", ref="#/components/schemas/User"),
 *      @OA\Property(property="post", type="object", description="The shared post", ref="#/components/schemas/Post"),
 * )
 */
class SharedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id'
    ];

    /**
     * Get the post that was shared.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who shared the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

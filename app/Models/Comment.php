<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Comment",
 *      title="Comment",
 *      description="Comment model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the comment"),
 *      @OA\Property(property="comment", type="string", description="Content of the comment"),
 *      @OA\Property(property="author_id", type="integer", format="int64", description="ID of the comment author"),
 *      @OA\Property(property="post_id", type="integer", format="int64", description="ID of the post to which the comment belongs"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="post", type="object", description="The post to which the comment belongs", ref="#/components/schemas/Post"),
 *      @OA\Property(property="author", type="object", description="The author of the comment", ref="#/components/schemas/User"),
 *      @OA\Property(property="likes", type="array", description="Array of likes for the comment", @OA\Items(ref="#/components/schemas/LikedComment")),
 * )
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment',
        'author_id',
        'post_id'
    ];

    /**
     * Get the post to which the comment belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the author of the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(LikedComment::class);
    }
}
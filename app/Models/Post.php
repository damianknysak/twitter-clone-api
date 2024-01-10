<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Post",
 *      title="Post",
 *      description="Post model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the post"),
 *      @OA\Property(property="title", type="string", description="Title of the post"),
 *      @OA\Property(property="slug", type="string", description="Slug of the post"),
 *      @OA\Property(property="author_id", type="integer", format="int64", description="ID of the post author"),
 *      @OA\Property(property="image", type="string", description="Image URL of the post"),
 *      @OA\Property(property="blur_hash", type="string", description="Blur hash of the post image"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="author", type="object", description="The author of the post", ref="#/components/schemas/User"),
 *      @OA\Property(property="comments", type="array", description="Array of post comments", @OA\Items(ref="#/components/schemas/Comment")),
 *      @OA\Property(property="likes", type="array", description="Array of liked posts", @OA\Items(ref="#/components/schemas/LikedPost")),
 * )
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'author_id',
        'image',
        'blur_hash'
    ];

    /**
     * Get the comments for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the author of the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(LikedPost::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}

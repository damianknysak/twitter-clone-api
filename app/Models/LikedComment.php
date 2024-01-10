<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="LikedComment",
 *      title="LikedComment",
 *      description="LikedComment model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the liked comment"),
 *      @OA\Property(property="user_id", type="integer", format="int64", description="ID of the user who liked the comment"),
 *      @OA\Property(property="comment_id", type="integer", format="int64", description="ID of the liked comment"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="user", type="object", description="The user who liked the comment", ref="#/components/schemas/User"),
 *      @OA\Property(property="comment", type="object", description="The liked comment", ref="#/components/schemas/Comment"),
 * )
 */
class LikedComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'comment_id'
    ];

    /**
     * Get the comment that was liked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the user who liked the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Follower",
 *      title="Follower",
 *      description="Follower model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the follower relationship"),
 *      @OA\Property(property="user_id", type="integer", format="int64", description="ID of the user being followed"),
 *      @OA\Property(property="follower_id", type="integer", format="int64", description="ID of the follower user"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="user", type="object", description="The user being followed", ref="#/components/schemas/User"),
 *      @OA\Property(property="follower", type="object", description="The follower user", ref="#/components/schemas/User"),
 * )
 */
class Follower extends Model
{
    use HasFactory;

    protected $table = 'follower_user';

    protected $fillable = [
        'user_id',
        'follower_id'
    ];

    /**
     * Get the user being followed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'follower_id', 'user_id')->withTimestamps();
    }

    /**
     * Get the follower user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function follower()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'user_id', 'follower_id')->withTimestamps();
    }
}
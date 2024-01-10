<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *      schema="User",
 *      title="User",
 *      description="User model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the user"),
 *      @OA\Property(property="name", type="string", description="Name of the user"),
 *      @OA\Property(property="nickname", type="string", description="Nickname of the user"),
 *      @OA\Property(property="email", type="string", format="email", description="Email of the user"),
 *      @OA\Property(property="profile_image", type="string", description="Profile image URL"),
 *      @OA\Property(property="blur_hash", type="string", description="Blur hash of the profile image"),
 *      @OA\Property(property="date_of_birth", type="string", format="date", description="Date of birth of the user"),
 *      @OA\Property(property="description", type="string", description="Description of the user"),
 *      @OA\Property(property="localization", type="string", description="Localization of the user"),
 *      @OA\Property(property="email_verified_at", type="string", format="date-time", description="Email verification timestamp"),
 *      @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *      @OA\Property(property="comments", type="array", description="Array of user comments", @OA\Items(ref="#/components/schemas/Comment")),
 *      @OA\Property(property="posts", type="array", description="Array of user posts", @OA\Items(ref="#/components/schemas/Post")),
 *      @OA\Property(property="likes", type="array", description="Array of liked posts", @OA\Items(ref="#/components/schemas/LikedPost")),
 *      @OA\Property(property="followings", type="array", description="Array of users followed by the current user", @OA\Items(ref="#/components/schemas/User")),
 *      @OA\Property(property="followers", type="array", description="Array of users following the current user", @OA\Items(ref="#/components/schemas/User")),
 * )
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'profile_image',
        'blur_hash',
        'date_of_birth',
        'description',
        'localization',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the comments for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    /**
     * Get the posts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get the liked posts for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(LikedPost::class);
    }

    /**
     * Get the users followed by the current user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'follower_id', 'user_id')->withTimestamps();
    }

    /**
     * Get the users following the current user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'user_id', 'follower_id')->withTimestamps();
    }
}

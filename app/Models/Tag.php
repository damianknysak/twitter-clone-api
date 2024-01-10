<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="Tag",
 *      title="Tag",
 *      description="Tag model",
 *      @OA\Property(property="id", type="integer", format="int64", description="ID of the tag"),
 *      @OA\Property(property="post_id", type="string", description="Post id"),
 *      @OA\Property(property="content", type="string", description="Content of the tag"),
 *      @OA\Property(property="post", type="object", description="Tagged post"),
 * )
 */

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'content'
    ];

    public function post()
    {
        return $this->belongsToMany(Post::class);
    }
}

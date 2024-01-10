<?php

namespace Database\Factories;

use App\Models\LikedPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LikedPost>
 */
class LikedPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function randomize_post_id()
    {
        return Post::all()->random()->id;
    }

    public function randomize_user_id()
    {
        return User::all()->random()->id;
    }

    public function definition(): array
    {

        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
        ];
    }
}

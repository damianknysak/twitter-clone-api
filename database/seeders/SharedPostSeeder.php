<?php

namespace Database\Seeders;

use App\Models\Follower;
use App\Models\Post;
use App\Models\SharedPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SharedPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //tworzenie retweetów do postów
        for ($i = 0; $i < 100; $i++) {
            $post_rand_id = Post::all()->random()->id;
            $user_rand_id = User::all()->random()->id;
            while (SharedPost::where('post_id', '=', $post_rand_id)->where(
                'user_id',
                '=',
                $user_rand_id
            )->first()) {
                $post_rand_id = Post::all()->random()->id;
                $user_rand_id = User::all()->random()->id;
            }
            SharedPost::create([
                'post_id' => $post_rand_id,
                'user_id' => $user_rand_id,
            ]);
        }
    }
}

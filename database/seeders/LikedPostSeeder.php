<?php

namespace Database\Seeders;

use App\Models\LikedPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikedPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //tworzenie lajków do postów
        for ($i = 0; $i < 1000; $i++) {
            $post_rand_id = Post::all()->random()->id;
            $user_rand_id = User::all()->random()->id;
            while (LikedPost::where('post_id', '=', $post_rand_id)->where(
                'user_id',
                '=',
                $user_rand_id
            )->first()) {
                $post_rand_id = Post::all()->random()->id;
                $user_rand_id = User::all()->random()->id;
            }
            LikedPost::factory()->create([
                'post_id' => $post_rand_id,
                'user_id' => $user_rand_id,
            ]);
        }
    }
}

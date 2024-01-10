<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\LikedComment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikedCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //tworzenie lajków do komentarzy
        for ($i = 0; $i < 1000; $i++) {
            $comment_rand_id = Comment::all()->random()->id;
            $user_rand_id = User::all()->random()->id;
            while (LikedComment::where('comment_id', '=', $comment_rand_id)->where(
                'user_id',
                '=',
                $user_rand_id
            )->first()) {
                $comment_rand_id = Comment::all()->random()->id;
                $user_rand_id = User::all()->random()->id;
            }
            LikedComment::factory()->create([
                'comment_id' => $comment_rand_id,
                'user_id' => $user_rand_id,
            ]);
        }
    }
}

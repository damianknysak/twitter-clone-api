<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(PostSeeder::class);
        $this->call(LikedPostSeeder::class);
        $this->call(LikedCommentSeeder::class);
        $this->call(FollowerSeeder::class);
        $this->call(SharedPostSeeder::class);
    }
}

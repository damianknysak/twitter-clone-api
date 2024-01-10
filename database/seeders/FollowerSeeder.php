<?php

namespace Database\Seeders;

use App\Models\Follower;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //tworzenie followerów do userów
        for ($i = 0; $i < 1000; $i++) {
            $user_rand = User::all()->random();
            $user1_rand = User::all()->random();


            $followings_user = Follower::where("follower_id", $user_rand->id)->get();


            if (!$followings_user->contains('user_id', $user1_rand->id)) {
                Follower::create([
                    'follower_id' => $user_rand->id,
                    'user_id' => $user1_rand->id,
                ]);
            }
        }
    }
}

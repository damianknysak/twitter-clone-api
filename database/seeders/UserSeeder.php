<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //tworzenie admina
        User::create([
            'name' => 'Damian Admin',
            'email' => 'admin@admin.com',
            'nickname' => 'admin',
            'password' => bcrypt('test12345678'),
            'profile_image' => 'profile_images/default_profile_image.png',
            'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC',
            'date_of_birth' => Carbon::createFromTimestamp(rand(strtotime('1980-01-01'), strtotime('2005-12-31')))
        ]);

        //test user
        User::create([
            'name' => 'Dominik Dąbek',
            'email' => 'dabek@dabek.com',
            'nickname' => 'dabkowski',
            'localization' => 'Rzerzęczyce',
            'description' => "Uwu uwu",
            'password' => bcrypt('test12345678'),
            'profile_image' => 'profile_images/default_profile_image.png',
            'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC',
            'date_of_birth' => Carbon::createFromTimestamp(rand(strtotime('1980-01-01'), strtotime('2005-12-31')))
        ]);

        //test user
        User::create([
            'name' => 'Kacper Płaczkiewicz',
            'email' => 'kapi@kapi.com',
            'nickname' => 'somsiad',
            'localization' => 'Częstochowa',
            'description' => "PHP Senior Developer",
            'password' => bcrypt('test12345678'),
            'profile_image' => 'profile_images/default_profile_image.png',
            'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC',
            'date_of_birth' => Carbon::createFromTimestamp(rand(strtotime('1980-01-01'), strtotime('2005-12-31')))
        ]);

        User::factory()->count(100)->create();
    }
}

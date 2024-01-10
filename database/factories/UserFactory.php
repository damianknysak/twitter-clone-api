<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = [
            'name' => fake()->name(),
            'nickname' => fake()->unique()->domainName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'profile_image' => 'profile_images/default_profile_image.png',
            'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC',
            'date_of_birth' => Carbon::createFromTimestamp(rand(strtotime('1980-01-01'), strtotime('2005-12-31'))),
            'description' => fake()->sentence(),
            'localization' => fake()->locale(),
        ];
        return $user;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;





/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public $tag_list = [
        "WolneKonopie", "Lewandowski", "StanFutbolu", "Rzycie", "Polityka",
        "Sport", "Skoki", "Zima", "Polska", "AntyPis", "Counter-Strike", "Popelina"
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_user_id = User::all()->random()->id;
        $random_tag = $this->tag_list[$random_user_id % count($this->tag_list)];
        return [
            'title' => $this->faker->sentence() . ' #' . $random_tag,
            'slug' => 'to-jest-slug',
            'author_id' => $random_user_id,
            'image' => $this->faker->image('public/storage', 640, 480, null, false),
            'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC'
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            $random_tag = $this->tag_list[$post->author->id % count($this->tag_list)];
            TagFactory::new()->withCustomValues($random_tag, $post->id)->create();
        });
    }
}

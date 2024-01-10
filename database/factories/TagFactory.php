<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->word, // Ustawiamy dowolną wartość dla content (może być dostosowane)
            'post_id' => $this->faker->randomNumber(), // Ustawiamy dowolną wartość dla post_id (może być dostosowane)
        ];
    }

    /**
     * Custom constructor for TagFactory.
     *
     * @param string $content
     * @param int $post_id
     * @return self
     */
    public function withCustomValues(string $content, int $post_id): self
    {
        return $this->state([
            'content' => $content,
            'post_id' => $post_id,
        ]);
    }
}

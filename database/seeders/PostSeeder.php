<?php

namespace Database\Seeders;

use App\Models\Post;
use Bepsvpt\Blurhash\Facades\BlurHash;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Post::factory()->count(5)
            ->hasComments(105)
            ->create();

        Post::factory()->count(2)
            ->hasComments(10)
            ->create();

        Post::factory()->count(4)
            ->hasComments(23)
            ->create();

        Post::factory()->count(10)
            ->hasComments(40)
            ->create();


        Post::factory()->count(20)
            ->hasComments(10)
            ->create();

        //dodawanie blura do kazdego zdjecia
        $posts = Post::all();
        foreach ($posts as $post) {
            $imagePath = $post->image; // Przyjmuję, że pole 'image' zawiera ścieżkę do obrazu

            // Przekształcenie ścieżki do instancji UploadedFile
            $uploadedFile = new UploadedFile(
                storage_path('app/public/' . $imagePath), // Pełna ścieżka do pliku
                pathinfo($imagePath, PATHINFO_BASENAME) // Oryginalna nazwa pliku
            );

            // Generowanie BlurHash na podstawie pliku
            $blur = BlurHash::encode($uploadedFile);

            // Aktualizuj pole hash_blur
            DB::table('posts')
                ->where('id', $post->id)
                ->update(['blur_hash' => $blur]);
        }
    }
}

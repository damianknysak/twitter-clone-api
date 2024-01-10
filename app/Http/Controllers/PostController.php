<?php

namespace App\Http\Controllers;

use App\Filters\PostFilter;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Bepsvpt\Blurhash\Facades\BlurHash;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


/**
 * Class PostController
 * @package App\Http\Controllers
 * @OA\Tag(
 *     name="Posts",
 *     description="Operations about posts"
 * )
 */
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

     /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Show all the posts",
     *     operationId="showPosts",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $filter = new PostFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) return new PostCollection(Post::orderBy('id', 'desc')->paginate());

            $posts = Post::orderBy('id', 'desc')->where($queryItems)->paginate();
            return new PostCollection($posts->appends($request->query()));
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     description="Store a new post in the database",
     *     operationId="addPost",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Post data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Sample Title"),
     *             @OA\Property(property="slug", type="string", example="Sample Content"),
     *             @OA\Property(property="image", type="string", format="image", description="Image file in webp format"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"tag1", "tag2"}),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not logged in",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     )
     * )
     */
    public function store(StorePostRequest $request)
    {
        try {
            if (!Auth::id()) return response()->json(["errors" => "You are not logged in"], 401);

            $data = $request->all();
            $data['author_id'] = Auth::id();

            if ($request['image'] && $request->hasfile('image')) {
                $file = $request->file('image');
                $image_name = time() . '.' . 'webp';
                Image::make($file)->resize(640, 480)->encode("webp")
                    ->save(public_path('/storage/' . $image_name));

                $blur = BlurHash::encode($file);
                $data['image'] = $image_name;
                $data['blur_hash'] = $blur;
            }

            $created_post = Post::create($data);

            //tags

            if ($request['tags']) {
                $tags_array = $request['tags'];

                foreach ($tags_array as $tag) {
                    $clone_tag = Tag::where('post_id', $created_post->id)
                        ->where('content', $tag)
                        ->first();
                    if (!$clone_tag) {
                        Tag::firstOrCreate(['content' => $tag, 'post_id' => $created_post->id]);
                    }
                }
            }

            return new PostResource($created_post);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Show a specific post",
     *     operationId="showPost",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to be retrieved",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     * )
     */
    public function show(Post $post)
    {
        try {
            return new PostResource($post);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

   /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update a specific post",
     *     description="Modify details of a specific post in the database",
     *     operationId="updatePost",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated post data",
     *         @OA\JsonContent(
     *             required={"title","slug"},
     *             @OA\Property(property="title", type="string", example="Sample Title"),
     *             @OA\Property(property="slug", type="string", example="Sample Content"),
     *             @OA\Property(property="image", type="file", format="image", description="Image file in webp format, can be null"),
     * 
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Post Updated Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to update this post",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     )
     * )
     */
    public function update(Request $request, Post $post)
    {
        try {
            if (Auth::id() != $post->author->id) {
                return response()->json(['message' => 'Its not your post'], 403);
            }
            $input = array_filter($request->all());
            if ($request['image'] && $request->hasfile('image')) {

                $file = $request->file('image');
                $image_name = time() . '.' . 'webp';
                Image::make($file)->resize(640, 480)->encode("webp")
                    ->save(public_path('/storage/' . $image_name));

                $blur = BlurHash::encode($file);
                $input['image'] = $image_name;
                $input['blur_hash'] = $blur;

                //delete old file
                if ($post->image && file_exists(public_path("storage/" . $post->image))) {
                    unlink(public_path("storage/" . $post->image));
                }
            }
            if ($request['tags']) {
                $tags_array = $request['tags'];
                $old_tags = $post->tags;
                // Remove old tags that are not present in the new set
                foreach ($old_tags as $old_tag) {
                    $old_tag->delete();
                }
                foreach ($tags_array as $tag) {
                    $clone_tag = Tag::where('post_id', $post->id)
                        ->where('content', $tag)
                        ->first();
                    if (!$clone_tag) {
                        $tagModel = Tag::firstOrCreate(['content' => $tag, 'post_id' => $post->id]);
                    }
                }
            }
            $post->update($input);
            return response()->json(['message' => "Post Edited Successfully!"]);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error updating post ' . $exception], 500);
        }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     tags={"Posts"},
     *     summary="Remove a specific post",
     *     description="Delete a specific post from the database",
     *     operationId="destroyPost",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Post Deleted Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to delete this post",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="string", description="Error message"),
     *         )
     *     ),
     * )
     */
    public function destroy(Post $post)
    {
        try {
            if ($post->author_id !== Auth::id()) {
                return response()->json(['message' => 'You are not authorized to delete this post.'], 403);
            }

            if ($post->image && file_exists(public_path("storage/" . $post->image))) {
                unlink(public_path("storage/" . $post->image));
            }
            $post->delete();
            return response()->json(['message' => 'Post deleted successfully']);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found'], 404);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error deleting post' . $exception], 500);
        }
    }
}

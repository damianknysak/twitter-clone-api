<?php

namespace App\Http\Controllers;

use App\Filters\LikedPostFilter;
use App\Http\Requests\StoreLikedPostRequest;
use App\Http\Requests\UpdateLikedPostRequest;
use App\Http\Resources\LikedPostCollection;
use App\Models\LikedPost;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
    * Class LikedPostController
    * @package App\Http\Controllers
    * @OA\Tag(
    *     name="LikedPosts",
    *     description="Operations about liked posts"
    * )
    */
class LikedPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/likedposts",
     *     tags={"LikedPosts"},
     *     summary="Show all the liked posts",
     *     operationId="showLikedposts",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Filter criteria (JSON format)",
     *         @OA\Schema(type="string")
     *     ),
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
            $filter = new LikedPostFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) {
                return new LikedPostCollection(LikedPost::orderBy('id', 'desc')->paginate());
            } else {
                $liked_posts = LikedPost::orderBy('id', 'desc')->where($queryItems)->paginate();
                return new LikedPostCollection($liked_posts);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

   /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/posts/{id}/like",
     *     tags={"LikedPosts"},
     *     summary="Like a post",
     *     description="Store a new liked post in the database",
     *     operationId="addPostLike",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to like",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like added successfully",
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
    public function store($id)
    {
        if (!Auth::id()) return response()->json(["errors" => "You are not logged in"], 401);
        try {
            $validator = Validator::make([
                'userId' => Auth::id(),
                'postId' => $id,
            ], [
                'userId' => ['required', Rule::exists('users', 'id')],
                'postId' => ['required', Rule::exists('posts', 'id')]
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => "Wrong Like Request"], 422);
            }

            $existingLikedPost = LikedPost::where('user_id', Auth::id())->where('post_id', $id)->first();
            $data = [
                'user_id' => Auth::id(),
                'post_id' => $id,
            ];

            if ($existingLikedPost) {
                return response()->json(['message' => "This post is already liked by this user"], 409);
            } else {

                LikedPost::create($data);
                return response()->json(['message' => "Like added successfully"], 200);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error"], 500);
        }
    }
     /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/posts/{id}/dislike",
     *     tags={"LikedPosts"},
     *     summary="Remove a specific like",
     *     description="Delete a specific like from the database",
     *     operationId="destroyPostLike",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to unlike",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Like Deleted Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to delete this like",
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
    public function destroy($id)
    {
        try {
            if (!Auth::id()) return response()->json(["errors" => "You are not logged in"], 401);

            $likedPost = LikedPost::where('post_id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            if ($likedPost) $likedPost->delete();

            return response()->json(['message' => 'Like deleted successfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Like not found'], 404);
        }
    }
}

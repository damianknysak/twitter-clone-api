<?php

namespace App\Http\Controllers;

use App\Filters\SharedPostFilter;
use App\Http\Requests\StoreSharedPostRequest;
use App\Http\Requests\UpdateSharedPostRequest;
use App\Http\Resources\SharedPostCollection;
use App\Models\SharedPost;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class SharedPostController
 * @package App\Http\Controllers
 * @OA\Tag(
 *     name="SharedPosts",
 *     description="Operations about shared posts"
 * )
 */
class SharedPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/sharedposts",
     *     tags={"SharedPosts"},
     *     summary="Show all shared posts",
     *     operationId="showSharedPosts",
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
            $filter = new SharedPostFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) {
                return new SharedPostCollection(SharedPost::orderBy('id', 'desc')->paginate());
            } else {
                $shared_posts = SharedPost::orderBy('id', 'desc')->where($queryItems)->paginate();
                return new SharedPostCollection($shared_posts);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/posts/{id}/share",
     *     tags={"SharedPosts"},
     *     summary="Share a post",
     *     description="Shares a post",
     *     operationId="addPostShare",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to share",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Share added successfully",
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
        try {
            $validator = Validator::make([
                'userId' => Auth::id(),
                'postId' => $id,
            ], [
                'userId' => ['required', Rule::exists('users', 'id')],
                'postId' => ['required', Rule::exists('posts', 'id')]
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => "Wrong Shared Post Request"], 422);
            }

            $existingSharedPost = SharedPost::where('user_id', Auth::id())->where('post_id', $id)->first();
            $data = [
                'user_id' => Auth::id(),
                'post_id' => $id,
            ];

            if ($existingSharedPost) {
                return response()->json(['message' => "This post is already shared by this user"], 409);
            } else {

                SharedPost::create($data);
                return response()->json(['message' => "Post shared successfully"], 200);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error"], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/posts/{id}/unshare",
     *     tags={"SharedPosts"},
     *     summary="Remove a share from specific post",
     *     description="Delete a specific share from the database",
     *     operationId="destroyPostShare",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to unshare",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Share deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Share Deleted Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to delete this share",
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

            $sharedPost = SharedPost::where('post_id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            if ($sharedPost) $sharedPost->delete();

            return response()->json(['message' => 'Post unshared successfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Shared post not found'], 404);
        }
    }
}

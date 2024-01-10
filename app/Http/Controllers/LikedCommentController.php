<?php

namespace App\Http\Controllers;

use App\Filters\LikedCommentFilter;
use App\Http\Resources\LikedCommentCollection;
use App\Models\LikedComment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
/**
    * Class LikedCommentController
    * @package App\Http\Controllers
    * @OA\Tag(
    *     name="LikedComments",
    *     description="Operations about liked comments"
    * )
    */
class LikedCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/likedcomments",
     *     tags={"LikedComments"},
     *     summary="Show all the liked comments",
     *     operationId="showLikedcomments",
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
            $filter = new LikedCommentFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) {
                return new LikedCommentCollection(LikedComment::orderBy('id', 'desc')->paginate());
            } else {
                $liked_comments = LikedComment::orderBy('id', 'desc')->where($queryItems)->paginate();
                return new LikedCommentCollection($liked_comments);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }


   /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/comments/{id}/like",
     *     tags={"LikedComments"},
     *     summary="Like a comment",
     *     description="Store a new liked comment in the database",
     *     operationId="addCommentLike",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to like",
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
        try {
            if (!Auth::id()) return response()->json(["errors" => "You are not logged in"], 401);

            $validator = Validator::make([
                'userId' => Auth::id(),
                'commentId' => $id,
            ], [
                'userId' => ['required', Rule::exists('users', 'id')],
                'commentId' => ['required', Rule::exists('comments', 'id')]
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => "Wrong Like Request"], 422);
            }

            $existingLikedComment = LikedComment::where('user_id', Auth::id())->where('comment_id', $id)->first();
            $data = [
                'user_id' => Auth::id(),
                'comment_id' => $id,
            ];

            if ($existingLikedComment) {
                return response()->json(['message' => "This comment is already liked by this user"], 409);
            } else {

                LikedComment::create($data);
                return response()->json(['message' => "Like added successfully"], 200);
            }
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/comments/{id}/dislike",
     *     tags={"LikedComments"},
     *     summary="Remove a specific like",
     *     description="Delete a specific like from the database",
     *     operationId="destroyCommentLike",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to unlike",
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

            $likedComment = LikedComment::where('comment_id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            if ($likedComment) $likedComment->delete();

            return response()->json(['message' => 'Like deleted successfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Like not found'], 404);
        }
    }
}

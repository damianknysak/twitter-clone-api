<?php

namespace App\Http\Controllers;

use App\Filters\CommentFilter;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentController
 * @package App\Http\Controllers
 * @OA\Tag(
 *     name="Comments",
 *     description="Operations about comments"
 * )
 */
class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/comments",
     *     tags={"Comments"},
     *     summary="Show all the comments",
     *     operationId="showComments",
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
            $filter = new CommentFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) {
                return new CommentCollection(Comment::orderBy('id', 'desc')->paginate());
            } else {
                //if not done with include next page loses filter
                $comments = Comment::orderBy('id', 'desc')->where($queryItems)->paginate();
                return new CommentCollection($comments->appends($request->query()));
            }
        } catch (Exception $error) {
            return response()->json(["errors" => "Server error"], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/comments",
     *     tags={"Comments"},
     *     summary="Create a new comment",
     *     description="Store a new comment in the database",
     *     operationId="addComment",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Comment data to be stored",
     *         @OA\JsonContent(
     *             required={"postId", "comment"},
     *             @OA\Property(property="postId", type="integer", description="ID of the associated post"),
     *             @OA\Property(property="comment", type="string", description="Content of the comment"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
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
    public function store(StoreCommentRequest $request)
    {
        try {
            if (!Auth::id()) return response()->json(["errors" => "You are not logged in"], 401);
            $data = [
                'author_id' => Auth::id(),
                'post_id' => $request->input('postId'),
                'comment' => $request->input('comment')
            ];

            return new CommentResource(Comment::create($data));
        } catch (Exception $error) {
            return response()->json(["errors" => "Server error"], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Show a specific comment",
     *     operationId="showComment",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to be retrieved",
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
    public function show(Comment $comment)
    {
        try {
            if ($comment) {
                return new CommentResource($comment);
            } else {
                return response()->json(['errors' => 'Not found.'], 404);
            }
        } catch (Exception $error) {
            return response()->json(["errors" => "Server error"], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Update a specific comment",
     *     description="Modify details of a specific comment in the database",
     *     operationId="updateComment",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated comment data",
     *         @OA\JsonContent(
     *             required={"comment"},
     *             @OA\Property(property="comment", type="string", description="Updated content of the comment"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Comment Updated Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to update this comment",
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
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        try {

            if ($comment->author_id != Auth::id()) {
                return response()->json(["errors" => "Unauthorized to do this action"], 401);
            }
            $comment->update($request->all());
            return response()->json(["message" => "Comment Updated Successfully"], 200);
        } catch (Exception $error) {
            return response()->json(["errors" => "Server error"], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     tags={"Comments"},
     *     summary="Remove a specific comment",
     *     description="Delete a specific comment from the database",
     *     operationId="destroyComment",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Comment Deleted Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to delete this comment",
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
    public function destroy(Comment $comment)
    {
        try {
            if ($comment->author_id != Auth::id()) {
                return response()->json(["errors" => "Unauthorized to do this action"], 401);
            }
            $comment->delete();
            return response()->json(["message" => "Comment Deleted Successfully"], 200);
        } catch (Exception $error) {
            return response()->json(["errors" => "Server error"], 500);
        }
    }
}

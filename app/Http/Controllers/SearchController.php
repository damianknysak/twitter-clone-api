<?php

namespace App\Http\Controllers;

use App\Filters\SearchFilter;
use App\Http\Resources\PostCollection;
use App\Http\Resources\UserCollection;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

/**
    * Class SearchController
    * @package App\Http\Controllers
    * @OA\Tag(
    *     name="Search",
    *     description="Operations about searching"
    * )
*/
class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     tags={"Search"},
     *     summary="Search for posts and users",
     *     description="Perform a search for posts and users based on a query string.",
     *     operationId="showSearch",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Search query",
     *         @OA\JsonContent(
     *             @OA\Property(property="q", type="string", example="search_query")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="query", type="string", example="search_query")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function find(Request $request)
    {
        try {
            $query_string = $request['q'];

            //Check if query for tag 
            $posts = null;
            if ($query_string && strlen($query_string) > 0 && $query_string[0] === '#') {

                $posts_with_tag = Tag::where("content", substr($query_string, 1))->pluck('post_id')->toArray();
                $posts = Post::whereIn('id', $posts_with_tag);
                $posts = new PostCollection($posts->orderByDesc('id')->take(15)->get());
            } else {
                //find post by title
                $posts = new PostCollection(Post::where('title', 'like', '%' . $query_string . '%')->orderByDesc('id')->take(15)->get());
            }

            // Find up to 5 users by name
            $users_found_by_partial_name = User::where('name', 'like', '%' . $query_string . '%')->take(5)->get();

            // Find up to 5 users by nickname
            $users_found_by_nickname = User::where('nickname', $query_string)->take(5)->get();

            // Merge the collections
            $merged_users = $users_found_by_partial_name->merge($users_found_by_nickname)->take(5);
            $result_object = ["query" => $query_string, "users" => new UserCollection($merged_users), "posts" => $posts];
            return $result_object;
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\FollowerCollection;
use App\Http\Resources\LikedPostCollection;
use App\Http\Resources\PostCollection;
use App\Http\Resources\SharedPostCollection;
use App\Http\Resources\UserCollection;
use App\Models\Follower;
use App\Models\LikedPost;
use App\Models\Post;
use App\Models\SharedPost;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


/**
    * Class FollowerController
    * @package App\Http\Controllers
    * @OA\Tag(
    *     name="Followers",
    *     description="Operations about follows"
    * )
*/
class FollowerController extends Controller
{
    
    /**
    * @OA\Get(
    *     path="/api/who-to-follow",
    *     tags={"Followers"},
    *     summary="Get suggestions for users to follow",
    *     description="Retrieve suggestions for users to follow based on the current user's activity.",
    *     operationId="showWhoToFollow",
    *     security={{"bearerAuth": {}}},
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\JsonContent(
    *             @OA\Property(property="data"),
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
    public function who_to_follow()
    {
        try {
            $current_user_followings = Follower::orderBy('id', 'desc')->where('follower_id', Auth::id())->get();
            $current_user_followings_ids = $current_user_followings->pluck('user_id')->toArray();
            array_push($current_user_followings_ids, Auth::id());

            $who_to_follow = User::whereNotIn('id', $current_user_followings_ids)
                ->inRandomOrder() // Get random users
                ->take(5)
                ->get();

            return new UserCollection($who_to_follow);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }
    /**
     * List all followers for a specific user.
     *
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/followers/{id}",
     *     tags={"Followers"},
     *     summary="Show all followers for specific user",
     *     operationId="showFollowers",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
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
     *         description="User not found",
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
    public function listFollowers($id)
    {
        try {
            return new FollowerCollection(Follower::orderBy('id', 'desc')->where("user_id", $id)->paginate());
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

     /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/activity-following",
     *     tags={"Followers"},
     *     summary="Show all follows",
     *     operationId="showFollows",
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
    public function activity_following()
    {
        try {
            //if post_id already exists as type post then dont show 

            $followings = Follower::orderBy('id', 'desc')->where('follower_id', Auth::id())->get();

            $array = [];

            foreach ($followings as $following) {
                $posts = new PostCollection(Post::orderBy('created_at', 'desc')->where('author_id', $following->user_id)->get());
                foreach ($posts as $post) {
                    $post->type = "post";
                }
                $sharedPosts = new SharedPostCollection(SharedPost::orderBy('created_at', 'desc')->where('user_id', $following->user_id)->get());
                foreach ($sharedPosts as $sharedPost) {
                    $sharedPost->type = "sharedpost";
                }
                $merged_array = $posts->concat($sharedPosts);
                $array = array_merge($array, $merged_array->toArray());
            }

            $collection = collect($array);

            // Sort by date
            $sortedCollection = $collection->sortByDesc('created_at');

            // delete retweet if there is a tweet with same post in collection
            foreach ($sortedCollection as $element) {
                if ($element->type == "post") {
                    $foundElementKey = $sortedCollection->search(function ($item) use ($element) {
                        return $item->post_id == $element->id && $item->type == "sharedpost";
                    });

                    if ($foundElementKey !== false) {
                        $sortedCollection->forget($foundElementKey);
                    }
                }
            }

            //pagination

            $perPage = 15;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $currentPageItems = $sortedCollection->slice(($currentPage - 1) * $perPage, $perPage);
            $paginatedActivity = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems->values()->all(),
                $sortedCollection->count(),
                $perPage,
                $currentPage
            );
            return response()->json($paginatedActivity->toArray(), 200);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error->getMessage()], 500);
        }
    }

    /**
     * List all people that a user is following.
     *
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/following/{id}",
     *     tags={"Followers"},
     *     summary="Show all users specific user follows",
     *     operationId="showFollowing",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
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
     *         description="User not found",
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
    public function listFollowings($id)
    {
        try {
            return new FollowerCollection(Follower::orderBy('id', 'desc')->where("follower_id", $id)->paginate());
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Follow a user.
     *
     * @param int $id User ID to follow
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Remove the specified resource from storage.
     *
     * @OA\Post(
     *     path="/api/users/{id}/follow",
     *     tags={"Followers"},
     *     summary="Add a specific follow",
     *     description="Add a specific follow to the database",
     *     operationId="addUserFollow",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to follow",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Followed Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to follow",
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
    public function follow($id)
    {
        try {
            $follower = auth()->user();

            if ($follower->id == $id) {
                return response()->json(["errors" => "You cannot follow yourself"], 422);
            }

            if ($follower->followings()->where('user_id', $id)->exists()) {
                return response()->json(["errors" => "You are already following this user"], 422);
            }

            $validator = Validator::make([
                'followerId' => Auth::id(),
                'userId' => $id,
            ], [
                'followerId' => ['required', Rule::exists('users', 'id')],
                'userId' => ['required', Rule::exists('users', 'id')]
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => "Invalid follow request"], 422);
            }

            $follower->followings()->attach($id);

            return response()->json(['message' => "You are now following the user"], 200);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Unfollow a user.
     *
     * @param int $id User ID to unfollow
     * @return \Illuminate\Http\JsonResponse
     */
     /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/users/{id}/unfollow",
     *     tags={"Followers"},
     *     summary="Remove a specific follow",
     *     description="Delete a specific follow from the database",
     *     operationId="destroyUserFollow",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to unfollow",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unfollowed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Unfollowed Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to unfollow",
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
    public function unfollow($id)
    {
        try {
            $follower = auth()->user();

            if (!$follower->followings()->where('user_id', $id)->exists()) {
                return response()->json(["errors" => "You are not following this user"], 422);
            }

            $validator = Validator::make([
                'followerId' => Auth::id(),
                'userId' => $id,
            ], [
                'followerId' => ['required', Rule::exists('users', 'id')],
                'userId' => ['required', Rule::exists('users', 'id')]
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => "Invalid unfollow request"], 422);
            }

            $follower->followings()->detach($id);

            return response()->json(['message' => "You have unfollowed the user"], 200);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }
}

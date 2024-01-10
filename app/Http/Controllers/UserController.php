<?php

namespace App\Http\Controllers;

use App\Filters\UserFilter;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\SharedPostCollection;
use App\Http\Resources\PostCollection;
use Intervention\Image\Facades\Image;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\SharedPost;
use App\Models\Post;
use App\Models\User;
use Bepsvpt\Blurhash\Facades\BlurHash;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


/**
 * Class UserController
 * @package App\Http\Controllers
 * @OA\Tag(
 *     name="Users",
 *     description="Operations about users"
 * )
 */
class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show', 'activity']]);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/activity/{id}",
     *     tags={"Users"},
     *     summary="Gets user activity",
     *     operationId="getUserActivity",
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
    public function activity(int $userId)
    {
        try {
            $posts = new PostCollection(Post::orderBy('created_at', 'desc')->where('author_id', $userId)->get());
            $sharedPosts = new SharedPostCollection(SharedPost::orderBy('created_at', 'desc')->where('user_id', $userId)->get());

            if ($posts->isEmpty() && $sharedPosts->isEmpty()) {
                return response()->json(['activity' => "not found"], 404);
            }

            $mergedPosts = $posts->concat($sharedPosts)->map(function ($item) {
                $item['type'] = ($item instanceof Post) ? 'post' : 'Sharedpost';
                return $item;
            });

            $sortedPosts = $mergedPosts->sortByDesc('created_at');

            $perPage = 15;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $currentPageItems = $sortedPosts->slice(($currentPage - 1) * $perPage, $perPage);
            $paginatedPosts = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems->values()->all(),
                $sortedPosts->count(),
                $perPage,
                $currentPage
            );

            return response()->json(['activity' => $paginatedPosts->toArray()], 200);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**  @OA\Get(
    *     path="/api/users",
    *     tags={"Users"},
    *     summary="Show all the users",
    *     operationId="showUsers",
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
            $filter = new UserFilter();
            $queryItems = $filter->transform($request);

            if (count($queryItems) == 0) return new UserCollection(User::orderBy('id', 'desc')->paginate());

            $users = User::orderBy('id', 'desc')->where($queryItems)->paginate();
            return new UserCollection($users->appends($request->query()));
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }


       /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Show a specific user",
     *     operationId="showUser",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to be retrieved",
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
    public function show(User $user)
    {
        try {
            return new UserResource($user);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update a specific user",
     *     description="Modify details of a specific user in the database",
     *     operationId="updateUser",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated comment data",
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", description="Updated name of the user"),
     *              @OA\Property(property="description", type="string", description="Updated description of the user"),
     *              @OA\Property(property="localization", type="string", description="Updated localization of the user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="User Updated Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to update this user",
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
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            if (Auth::id() != $user->id) {
                return response()->json(['message' => 'You are not the user that you want to update' . Auth::id()], 401);
            }

            $input = array_filter($request->all());

            if ($request->hasfile('profileImage')) {

                $file = $request->file('profileImage');
                $image_name = time() . '.' . 'webp';

                $profile_images_path = public_path("/storage/profile_images/");

                if (!File::exists($profile_images_path)) File::makeDirectory($profile_images_path, 775);

                Image::make($file)->resize(640, 480)->encode("webp")
                    ->save(public_path('/storage/profile_images/' . $image_name));

                $blur = BlurHash::encode($file);
                $input['profile_image'] = '/profile_images/' . $image_name;
                $input['blur_hash'] = $blur;

                //delete old file
                if (file_exists(public_path("storage/profile_images/" . $user->profile_image)) && $user->profile_image != "profile_images/default_profile_image.png") {
                    unlink(public_path("storage/profile_images/" . $user->profile_image));
                }
            }
            //date fixes
            if (isset($input['dateOfBirth'])) {
                $input['date_of_birth'] = date('Y-m-d H:i:s', $input['dateOfBirth']);
            }

            $user->update($input);
            return response()->json(['message' => 'User updated successfully']);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error updating user ' . $exception], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Remove a specific user",
     *     description="Delete a specific user from the database",
     *     operationId="destroyUser",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="User Deleted Successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: User not allowed to delete this user",
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
    public function destroy(User $user)
    {
        try {
            if (!Auth::id() === $user->id) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            //delete old image file
            if (file_exists(public_path("storage/profile_images/" . $user->profile_image)) && $user->profile_image != "profile_images/default_profile_image.png") {
                unlink(public_path("storage/profile_images/" . $user->profile_image));
            }
            $user->delete();
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Error deleting post'], 500);
        }
    }
}

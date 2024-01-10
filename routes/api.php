<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikedCommentController;
use App\Http\Controllers\LikedPostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SharedPostController;
use App\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::group(['namespace' => 'App\Http\Controllers'], function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class);
    Route::apiResource('likedposts', LikedPostController::class);
    Route::apiResource('likedcomments', LikedCommentController::class);
    Route::apiResource('sharedposts', SharedPostController::class);

    Route::apiResource('users', UserController::class);
    Route::get('activity/{userId}', [UserController::class, 'activity']);

    Route::get('followers/{id}', [FollowerController::class, 'listFollowers']);
    Route::get('following/{id}', [FollowerController::class, 'listFollowings']);

    Route::get("tags/trending", [TagController::class, "trending_tags"]);
    Route::get('search', [SearchController::class, 'find']);
});

Route::group(['namespace' => 'App\Http\Controllers', 'middleware' => "auth:sanctum"], function () {

    Route::get('who-to-follow', [FollowerController::class, 'who_to_follow']);
    Route::get('activity-following', [FollowerController::class, 'activity_following']);

    Route::post('posts/{id}/like', [LikedPostController::class, 'store']);
    Route::delete('posts/{id}/dislike', [LikedPostController::class, 'destroy']);


    Route::post('comments/{id}/like', [LikedCommentController::class, 'store']);
    Route::delete('comments/{id}/dislike', [LikedCommentController::class, 'destroy']);


    Route::post('users/{id}/follow', [FollowerController::class, 'follow']);
    Route::post('users/{id}/unfollow', [FollowerController::class, 'unfollow']);


    Route::post('posts/{id}/share', [SharedPostController::class, 'store']);
    Route::delete('posts/{id}/unshare', [SharedPostController::class, 'destroy']);


    //to be continued ...
});

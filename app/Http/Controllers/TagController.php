<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;

/**
    * Class TagController
    * @package App\Http\Controllers
    * @OA\Tag(
    *     name="Tags",
    *     description="Operations about tags"
    * )
*/
class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/tags/trending",
     *     tags={"Tags"},
     *     summary="Get trending tags",
     *     description="Retrieve a list of trending tags based on their usage.",
     *     operationId="showTrendingTags",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="tags", type="array", @OA\Items(
     *                 @OA\Property(property="content", type="string", example="tag1"),
     *                 @OA\Property(property="tag_count", type="integer", example=10)
     *             )),
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
    public function trending_tags()
    {
        $tags = Tag::groupBy('content')
            ->select('content', DB::raw('count(*) as tag_count'))
            ->orderByDesc('tag_count')
            ->take(10)
            ->get();
        return  $tags;
    }
}

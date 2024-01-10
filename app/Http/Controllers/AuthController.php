<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthController
 * @package App\Http\Controllers
 * @OA\Tag(
 *     name="Authorization",
 *     description="Login in register"
 * )
 */
class AuthController extends Controller
{
     /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authorization"},
     *     summary="Create a new user",
     *     description="Store a new user in the database",
     *     operationId="addUser",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to be stored",
     *         @OA\JsonContent(
     *             required={"name", "nickname","email","password"},
     *             @OA\Property(property="name", type="string", description="New user name"),
     *             @OA\Property(property="nickname", type="string", description="New user nickname"),
     *             @OA\Property(property="email", type="string", description="New user email"),
     *             @OA\Property(property="password", type="string", description="New user password")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data"),
     *          )
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
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'nickname' => $request['nickname'],
                'password' => bcrypt($request['password']),
                'profile_image' => 'profile_images/default_profile_image.png',
                'blur_hash' => 'LOI~3_WB~pWB_3ofIUj[00fQ00WC',
            ]);

            $token = $user->createToken('user-token')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token
            ];

            return Response($response, 201);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }
     /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authorization"},
     *     summary="Login",
     *     description="Allows to login into service",
     *     operationId="loginUser",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to login",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", description="User email"),
     *             @OA\Property(property="password", type="string", description="User password")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data"),
     *         )
     *     ),
     *      @OA\Response(
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
    public function login(LoginRequest $request)
    {

        try {
            $user = User::where('email', $request['email'])->first();
            if (!$user || !Hash::check($request['password'], $user->password)) {
                return response([
                    "message" => "Wrong credentials"
                ], 401);
            }

            $token = $user->createToken('user-token')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token
            ];

            return Response($response, 201);
        } catch (Exception $error) {
            return response()->json(['errors' => "Server error " . $error], 500);
        }
    }
}

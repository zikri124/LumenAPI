<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Empty field',
            ], 400);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = Hash::make($request->input('password'));

        $emailDB = User::where('email', $email)->first();

        if ($emailDB) {
            return response()->json([
                'success' => False,
                'message' => 'Email already exist'
            ], 400);
        }

        $register = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        if ($register) {
            $jwt = JWT::encode(
                [
                    "sub" => $register->email,
                    "name" => $register->name,
                    "iat" => time(),
                    "role" => "user"
                ],
                env('JWT_KEY', 'secret'),
                'HS256'
            );

            $register->save();

            return response()->json([
                'success' => true,
                'message' => 'Register Success!',
                'data' => [
                    'token' => $jwt
                ]
            ], 201);
        }
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if ($user) {
            if (Hash::check($password, $user->password)) {
                $jwt = JWT::encode(
                    [
                        "sub" => "{$user->id}:{$user->email}",
                        "name" => $user->name,
                        "iat" => time(),
                        "role" => $user->role
                    ],
                    env('JWT_KEY', 'secret'),
                    'HS256'
                );

                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully LogIn!',
                    'data' => [
                        'token' => $jwt
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Password doesnt match!',
                    'data' => ''
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }
}

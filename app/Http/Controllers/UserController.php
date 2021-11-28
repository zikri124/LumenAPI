<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;

class UserController extends Controller
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

    public function getAllUsers(Request $request)
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'message' => 'Success get all users',
            'data' => [
                'users' => $users
            ]
        ], 200);
    }

    public function showOneUser(Request $request, $userId)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $role = $jwt->role;

        $user = $role == 'admin' ? User::find($userId)
            : User::where('email', $email)->first();

        if ($user) {
            if ($user->role != 'user') {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully get user',
                    'data' => ['user' => $user]
                ], 200);
            } else if ($user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully get user',
                    'data' => ['user' => $user]
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function updateUser(Request $request, $userId)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $editor = User::where('email', $email)->first();
        $user = User::find($userId);
        $user->name = $request->name;
        $user->save;

        if ($user) {
            if ($userId == $editor->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Updated',
                    'data' => ['user' => $user]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed Update User'
            ], 404);
        }
    }

    public function deleteUser(Request $request, $userId)
    {
        $token = $request->bearerToken();
        $jwt = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        $email = $jwt->sub;
        $user = User::where('email', $email)->first();

        if ($user->id == $userId) {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden access to delete other user'
            ], 403);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $data = $request->validated();
        $user_registed = User::create($data);
        $token = $user_registed->createToken($data['email']);
        $user_registed->token = $token->plainTextToken;
        $user_registed->message = "Register success";

        return (new UserResource($user_registed))->response()
            ->setStatusCode(200);
    }

    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (Hash::check($data['password'], $user->password ?? null)) {
            $token = $user->createToken($data['email']);
            $user->token = $token->plainTextToken;

            $user->message = "Login success";

            return (new UserResource($user))->response()
                ->setStatusCode(200);
        }

        return response()->json([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'message' => 'Email or Password is not valid'
            ]
        ], 400);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();


        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Logout success'
        ], 200);
    }

    public function getUser(Request $request)
    {
        $request->user()->message = "Get user success";
        return (new UserResource($request->user()))->response()
            ->setStatusCode(200);
    }

    public function updateUser(Request $request)
    {
        $data = $request->validate([
            "old_password" => "required",
            "new_password" => "required"
        ]);

        if (Hash::check($data['old_password'], $request->user()->password)) {
            $request->user()->update([
                "password" => Hash::make($data['new_password'])
            ]);

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update password success'
            ], 200);
        }

        return response()->json([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'message' => 'Old password is not valid'
            ]
        ], 400);
    }

    public function deleteUser(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Delete user success'
        ], 200);
    }
}

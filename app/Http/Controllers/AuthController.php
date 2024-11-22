<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Category;
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

        $category_registed = [
            [
                'name'        => 'Makanan',
                'description' => 'Pengeluaran untuk makanan',
                'budget'      => null,
                'type'        => 'Pengeluaran',
                'user_id'     => $user_registed->id
            ],
            [
                'name'        => 'Gaji',
                'description' => 'Pemasukan dari gaji',
                'budget'      => null,
                'type'        => 'Pemasukan',
                'user_id'     => $user_registed->id
            ],
            [
                'name'        => 'Belanja',
                'description' => 'Pengeluaran untuk belanja',
                'budget'      => null,
                'type'        => 'Pengeluaran',
                'user_id'     => $user_registed->id
            ]
        ];

        Category::insert($category_registed);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Register success',
            'data'    => new UserResource($user_registed)
        ], 200);
    }

    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (Hash::check($data['password'], $user->password ?? null)) {
            $token = $user->createToken($data['email']);
            $user->token = $token->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Login success',
                'data'    => new UserResource($user)
            ], 200);
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

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get user success',
            'data'    => new UserResource($request->user())
        ], 200);
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

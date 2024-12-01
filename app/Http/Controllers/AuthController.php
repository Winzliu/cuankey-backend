<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use DB;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $user_registed = User::create($data);
            $token = $user_registed->createToken($data['email']);
            $user_registed->token = $token->plainTextToken;

            $category_registed = [
                [
                    'name'        => 'Food & Beverage',
                    'description' => 'Cost for food and beverage',
                    'budget'      => null,
                    'type'        => 'Pengeluaran',
                    'user_id'     => $user_registed->id
                ],
                [
                    'name'        => 'Salary',
                    'description' => 'Income from salary',
                    'budget'      => null,
                    'type'        => 'Pemasukan',
                    'user_id'     => $user_registed->id
                ],
                [
                    'name'        => 'Groceries',
                    'description' => 'Cost for groceries',
                    'budget'      => null,
                    'type'        => 'Pengeluaran',
                    'user_id'     => $user_registed->id
                ]
            ];

            Category::insert($category_registed);

            $wallet_registed = [
                [
                    "name"            => "Cash Wallet",
                    "initial_balance" => 0,
                    "is_active"       => 1,
                    'user_id'         => $user_registed->id
                ],
                [
                    "name"            => "Bank Wallet",
                    "initial_balance" => 0,
                    "is_active"       => 0,
                    'user_id'         => $user_registed->id
                ]
            ];

            Wallet::insert($wallet_registed);
            $user_registed->profile_picture = 1;

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Register success',
                'data'    => new UserResource($user_registed)
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Input data is not valid',
                'errors'  => [
                    'message' => $th->getMessage()
                ]
            ]);
        }
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
            "fullname"        => "sometimes|string|max:255|min:3",
            "phone_number"    => "sometimes|numeric|digits_between: 8,15",
            "profile_picture" => "sometimes|numeric|in:1,2,3,4,5,6,7,8"
        ]);

        $request->user()->update($data);

        $user = $request->user();
        $user->token = null;

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Update user success',
            'data'    => new UserResource($user)
        ], 200);
    }

    public function updateUserPassword(Request $request)
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

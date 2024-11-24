<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    private function walletQuery()
    {
        return Wallet::with(['transactions.category'])
            ->withSum(['transactions as total_income' => function ($query) {
                $query->whereHas('category', fn($q) => $q->where('type', 'Pemasukan'));
            }], 'amount')
            ->withSum(['transactions as total_outcome' => function ($query) {
                $query->whereHas('category', fn($q) => $q->where('type', 'Pengeluaran'));
            }], 'amount');
    }


    public function getWallet(Request $request)
    {
        $wallets = $this->walletQuery()
            ->whereIn('user_id', [$request->user()->id, 0])
            ->get();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Wallet success',
            'data'    => WalletResource::collection($wallets)
        ], 200);
    }
    
    public function getWalletById($id)
    {
        $wallet = $this->walletQuery()->find($id);

        if ($wallet) {
            $wallet->total_balance = ($wallet->initial_balance ?? 0) + ($wallet->total_income ?? 0) - ($wallet->total_outcome ?? 0);
        }

        if (!$wallet) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Wallet not found'
            ], 404);
        }

        if (Gate::denies('private', $wallet)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see your own wallet.'
            ], 403);
        }

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get wallet success',
            'data'    => new WalletResource($wallet)
        ], 200);
    }

    public function getAllWalletTotalBalance(Request $request)
    {
        $wallets = $this->walletQuery()
        ->whereIn('user_id', [$request->user()->id, 0])
        ->get();

        $walletResources = WalletResource::collection($wallets)->toArray($request);

        $totalBalance = array_sum(array_column($walletResources, 'total_balance'));

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get All Wallet Total Balance Successfully',
            'data'    => [
                'total_balance' => $totalBalance,
                'user'          => [
                    'fullname'     => $request->user()->fullname,
                    'phone_number' => $request->user()->phone_number,
                    'email'        => $request->user()->email,
                ],
            ]
        ], 200);
    }

    public function createWallet(WalletRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $wallet = Wallet::where('user_id', $data['user_id'])->where('name', $data['name'])->first();

        if ($wallet) {
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Input data is not valid',
                'errors'  => [
                                'name' => 'Wallet name already exists'
                            ]
            ]);
        }

        
        if (!$wallet) {
            $hasActiveWallet = Wallet::where('user_id', $data['user_id'])->where('is_active', true)->exists();
            $data['is_active'] = !$hasActiveWallet;
            $wallet = Wallet::create($data);

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Create wallet success',
                'data'    => new WalletResource($wallet)
            ]);
        }

        return response([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'name' => 'wallet name already exist'
            ]
        ]);
    }

    public function updateWallet(WalletRequest $request, $id)
    {
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Wallet.'
            ], 403);
        }

        $data = $request->validated();

        $wallet = Wallet::find($id);
        if ($wallet) {
            $wallet->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Wallet success',
                'data'    => new WalletResource($wallet)
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Wallet not found'
        ], 404);
    }

    public function switchWallet($id)
    {
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only switch your own Wallet.'
            ], 403);
        }

        $wallet = Wallet::find($id);
        if ($wallet) {
            Wallet::where('user_id', $wallet->user_id)->update(['is_active' => false]);

            $wallet->update(['is_active' => true]);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Wallet switched successfully',
                'data'    => new WalletResource($wallet)
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Wallet not found'
        ], 404);
    }      

    public function deleteWallet($id)
    {
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own wallet.'
            ], 403);
        }

        $wallet = Wallet::find($id);
        if ($wallet) {
            $wallet->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete wallet success'
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'wallet not found'
        ], 404);
    }
}

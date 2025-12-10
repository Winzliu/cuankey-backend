<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    /*
        Bagian ini mendefinisikan fungsi untuk membangun 
        query dompet dengan data transaksi terkait
    */
    private function walletQuery()
    {
        return Wallet::with(['transactions.category'])
            // Menjumlahkan total pemasukan berdasarkan kategori transaksi
            ->withSum(['transactions as total_income' => function ($query) {
                $query->whereHas('category', fn($q) => $q->where('type', 'Pemasukan'));
            }], 'amount')
            // Menjumlahkan total pengeluaran berdasarkan kategori transaksi
            ->withSum(['transactions as total_expense' => function ($query) {
                $query->whereHas('category', fn($q) => $q->where('type', 'Pengeluaran'));
            }], 'amount');
    }

    /*
        FUNGSI UNTUK MGNAMBIL SEMUA DOMPET MILIK PENGGUNA BERDASARKAN USER ID
    */
    public function getWallet(Request $request)
    {
        $wallets = $this->walletQuery()
            ->whereIn('user_id', [$request->user()->id, 0])
            ->get();

        // pesan sukses
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Wallet success',
            'data'    => WalletResource::collection($wallets)
        ], 200);
    }
    
    /*
        FUNGSI MENDAPATKAN WALLET BERDASARKAN ID
    */
    public function getWalletById($id)
    {
        $wallet = $this->walletQuery()->find($id);

        //menambahkan perhitungan saldo total jika dompet ditemukan 
        if ($wallet) {
            $wallet->total_balance = ($wallet->initial_balance ?? 0) + ($wallet->total_income ?? 0) - ($wallet->total_expense ?? 0);
        }

        // mengembalikan pesan kesalahan jika dompet tidak ditemukan
        if (!$wallet) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Wallet not found'
            ], 404);
        }

        //  mengecek apakah pengguna memiliki akses ke dompet
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

    /*
        FUNGSI TOTAL SALDO DOMPET PENGGUNA
    */
    public function getAllWalletTotalBalance(Request $request)
    {
        $wallets = $this->walletQuery()
        ->whereIn('user_id', [$request->user()->id, 0])
        ->get();

        // mengonversi data dompet ke resource dan menghidung saldo total
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

    /*
        FUNGSI MENAMBAH DOMPET BARU UNTUK PENGGUNA
    */
    public function createWallet(WalletRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['initial_balance'] = $data['initial_balance'] ?? 0;

        // MEMERIKSA APAKAH DOMPET DENGAN NAMA YANG SAMA SUDAH ADA
        $wallet = Wallet::where('user_id', $data['user_id'])->where('name', $data['name'])->first();

        // pesan error pada dompet duplikat
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

        // menentukan apakah dompet baru harus menjadi dompet aktif
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

    /*
        FUNGSI UPDATE WALLET
    */
    public function updateWallet(WalletRequest $request, $id)
    {
        // cek apakah user memiliki akses berdasarkan id
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Wallet.'
            ], 403);
        }

        // validasi data
        $data = $request->validated();

        // mencari wallet berdasarkan id
        $wallet = Wallet::find($id);
        if ($wallet) {
            // update wallet
            $wallet->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Wallet success',
                'data'    => new WalletResource($wallet)
            ]);
        }
        // pesan error jika tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Wallet not found'
        ], 404);
    }

    /*
        FUNGSI GANTI WALLET
    */
    public function switchWallet($id)
    {
        // cek izin user apakah memiliki otorisasi terhadap wallet
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only switch your own Wallet.'
            ], 403);
        }

        // mencari wallet berdasarkan ID
        $wallet = Wallet::find($id);
        if ($wallet) {
            Wallet::where('user_id', $wallet->user_id)->update(['is_active' => false]);
            // update wallet aktif sesuai yang dipilih
            $wallet->update(['is_active' => true]);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Wallet switched successfully',
                'data'    => new WalletResource($wallet)
            ]);
        }

        // pesan error jika tidak ditemukan 
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Wallet not found'
        ], 404);
    }      

    /*
        FUNGSI DELETE WALLET
    */
    public function deleteWallet($id)
    {
        // cek user apakah memiliki izin terhadap wallet
        if (Gate::denies('private', Wallet::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own wallet.'
            ], 403);
        }

        // mencari wallet berdasarkan id
        $wallet = Wallet::find($id);
        if ($wallet) {
            // delete wallet
            $wallet->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete wallet success'
            ]);
        }
        
        // pesan error jika tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'wallet not found'
        ], 404);
    }
}

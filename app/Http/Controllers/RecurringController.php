<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringRequest;
use App\Http\Resources\RecurringResource;
use App\Models\Recurring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecurringController extends Controller
{
    /*
        FUNGSI FITUR PERULANGAN TRANSAKSI OLEH USER
    */
    public function getRecurring(Request $request)
    {
        // Mendapatkan seluruh transaksi / keuangan perulangan otomatis sesuai user id
        $recurring = Recurring::whereIn('user_id', [$request->user()->id])->get();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Recurring success',
            'data'    => RecurringResource::collection($recurring)
        ], 200);
    }

    public function getRecurringById($id)
    {
        // Cari transaksi perulangan berdasarkan ID
        $recurring = Recurring::find($id);
        // Kembalikan pesan jika transaksi tidak ditemukan
        if (!$recurring) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Recurring not found'
            ], 404);
        }
        // cek user apakah memiliki akses untuk ke recurring
        if (Gate::denies('private', $recurring)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see your own Recurring.'
            ], 403);
        }

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Recurring success',
            'data'    => new RecurringResource($recurring)
        ], 200);
    }

    /*
        FUNGSI UNTUK FITUR MENAMBAHKAN RECURRING
    */
    public function createRecurring(RecurringRequest $request)
    {
        // ambil data yang telah divalidasi
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        // periksa apakah transaksi perulangan serupa sudah ada untuk user ini
        $existingRecurring = Recurring::where('user_id', $data['user_id'])
            ->where('wallet_id', $data['wallet_id'])
            ->where('category_id', $data['category_id'])
            ->where('description', $data['description'])
            ->first();

        // Jika iya akan diberi pesan error duplikat
        if ($existingRecurring) {
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Duplicate recurring transaction found for this user',
                'errors'  => [
                    'recurring' => 'A recurring transaction with these details already exists for this user.'
                ]
            ], 400);
        }

        // Buat transaksi baru
        $recurring = Recurring::create($data);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Create Recurring success',
            'data'    => new RecurringResource($recurring)
        ]);
    }

    /*
            FUNGSI UPDATE FITUR RECURRING
    */
    public function updateRecurring(RecurringRequest $request, $id)
    {
        // Cek apakah user memiliki akses
        if (Gate::denies('private', Recurring::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Recurring Transaction.'
            ], 403);
        }

        // Validasi data yang dipilih
        $data = $request->validated();

        // Mencari Recurring ID di database untuk diupdate
        $recurring = Recurring::find($id);
        if ($recurring) {
            // mengupdate recurring
            $recurring->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Recurring Transaction success',
                'data'    => new RecurringResource($recurring)
            ]);
        }

        // memberi pesan error jika tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }
    /*
        FUNGSI DELETE RECURRING
    */
    public function deleteRecurring($id)
    {
        // Cek apakah user memiliki akses untuk mencari recurring berdasarkan policies
        if (Gate::denies('private', Recurring::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Recurring Transaction.'
            ], 403);
        }

        // mencari recurring by id
        $Recurring = Recurring::find($id);
        if ($Recurring) {
            // delete recurring atau transaksi perulangan
            $Recurring->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete Recurring Transaction success'
            ]);
        }
        // memberi pesan error jika gagal
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Recurring not found'
        ], 404);
    }
}

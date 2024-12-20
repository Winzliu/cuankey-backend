<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    /*
        FUNGSI UNTUK MENGAMBIL SELURUH TRANSAKSI YANG ADA
    */
    public function getTransaction(Request $request)
    {
        // mengambil seluruh transaksi berdasarkan user_id
        $transactions = Transaction::whereIn('user_id', [$request->user()->id])->orderBy('transaction_date','desc')->orderBy('created_at', 'desc')->get();

        // memberi pesan sukses
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Transaction success',
            'data'    => TransactionResource::collection($transactions)
        ], 200);
    }

    /*
        FUNGSI REPORT BULANAN DAN MENGAMBIL SELURUH TRANSAKSI 
        SELAMA 5 BULAN TERAKHIR
    */
    public function getTransactionPerMonth(Request $request)
    {
        // mengambil tanggal hari ini dan menyesuaikan formatnya dengan carbon
        $currentDate = Carbon::now();
        // mengambil tanggal awal bulan
        $startDate = $currentDate->copy()->subMonths(4)->startOfMonth();
        // mengambil tanggal akhir bulan
        $endDate = $currentDate->endOfMonth();

        // mengambil seluruh transaksi berdasarkan user id
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        // inisialisasi keperluan report transaksi bulanan
        $monthlyReport = [];
        $totalIncomeAmount = 0;
        $totalIncomeCount = 0;
        $totalExpenseAmount = 0;
        $totalExpenseCount = 0;

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);

        foreach ($period as $date) {
            $monthKey = $date->format('Y-m');
            $monthlyReport[$monthKey] = [
                'month' => $date->format('F Y'),
                'total_income' => 0,
                'total_expense' => 0,
            ];
        }

        // kalkulasi seluruh transaksi yang didapatkan ke dalam pemasukan dan pengeluaran
        foreach ($transactions as $transaction) {
            $monthKey = Carbon::parse($transaction->transaction_date)->format('Y-m');
            $categoryType = $transaction->category->type;

            if ($categoryType == 'Pemasukan') {
                $monthlyReport[$monthKey]['total_income'] += $transaction->amount;
                $totalIncomeAmount += $transaction->amount;
                $totalIncomeCount++;
            } elseif ($categoryType == 'Pengeluaran') {
                $monthlyReport[$monthKey]['total_expense'] += $transaction->amount;
                $totalExpenseAmount += $transaction->amount;
                $totalExpenseCount++;
            }
        }

        // kalkulasi pendapatan dan pengeluaran rata-rata
        $overallIncomeAverage = $totalIncomeCount > 0 ? $totalIncomeAmount / $totalIncomeCount : 0;
        $overallExpenseAverage = $totalExpenseCount > 0 ? $totalExpenseAmount / $totalExpenseCount : 0;

        // mendapatkan hasil report selama 5 bulan terakhir
        for ($i = 0; $i < 5; $i++) {
            $targetDate = $currentDate->copy()->subMonths($i);
            $monthKey = $targetDate->format('Y-m');

            if (!isset($monthlyReport[$monthKey])) {
                $monthlyReport[$monthKey] = [
                    'month' => $targetDate->format('F Y'),
                    'total_income'  => 0,
                    'total_expense' => 0,
                ];
            }
        }

        $monthlyReport = array_reverse($monthlyReport);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Last 5 Months Report Retrieved Successfully',
            'data'      => array_values($monthlyReport),
            'user'      => [
                    'fullname'      => $request->user()->fullname,
                    'phone_number'  => $request->user()->phone_number,
                    'email'         => $request->user()->email,
                ],
            'overall_averages'      => [
                'average_income'    => $overallIncomeAverage,
                'average_expense'   => $overallExpenseAverage,
            ],
        ], 200);
    }

    /*
        FUNGSI MENDAPATKAN TRANSAKSI BY ID
    */
    public function getTransactionById($id)
    {
        $transaction = Transaction::find($id);
        // pesan error jika tidak ditemukan
        if (!$transaction) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Transaction not found'
            ], 404);
        }
        // pesan error jika user tidak memiliki akses
        if (Gate::denies('private', $transaction)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see your own Transaction.'
            ], 403);
        }
        // pesan berhasil
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Transaction success',
            'data'    => new TransactionResource($transaction)
        ], 200);
    }

    /*
        FUNGSI MEMBUAT TRANSAKSI
    */
    public function createTransaction(TransactionRequest $request)
    {
        // validasi data dan meminta user id
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        // mendapatkan seluruh transaksi yang pernah dilakukan berdasarkan user id
        $existingTransaction = Transaction::where('user_id', $data['user_id'])
            ->where('wallet_id', $data['wallet_id'])
            ->where('category_id', $data['category_id'])
            ->where('description', $data['description'])
            ->first();
        // pesan error jika transaksi baru adalah duplikat
        if ($existingTransaction) {
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Duplicate transaction found for this user',
                'errors'  => [
                    'transaction' => 'A transaction with these details already exists for this user.'
                ]
            ], 400);
        }
        // membuat transaksi baru
        $transaction = Transaction::create($data);
        // pesan berhasil
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Create Transaction success',
            'data'    => new TransactionResource($transaction)
        ]);
    }

    /*
        FUNGSI UPDATE TRANSAKSI
    */
    public function updateTransaction(TransactionRequest $request, $id)
    {
        // cek apakah user memiliki hak akses untuk update transaksi
        if (Gate::denies('private', Transaction::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Transaction.'
            ], 403);
        }

        // validasi data
        $data = $request->validated();

        // Mencari transaksi yang ingin diperbarui
        $Transaction = Transaction::find($id);
        if ($Transaction) {
            // mengupdate transaksi
            $Transaction->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Transaction success',
                'data'    => new TransactionResource($Transaction)
            ]);
        }
        // pesan error jika tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }

    /*
        FUNGSI MENGHAPUS TRANSAKSI
    */
    public function deleteTransaction($id)
    {
        // cek apakah user memiliki akses untuk menghapus transaksi
        if (Gate::denies('private', Transaction::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Transaction.'
            ], 403);
        }

        // mencari transaksi berdasarkan ID
        $Transaction = Transaction::find($id);
        if ($Transaction) {
            // menghapus transaksi
            $Transaction->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete Transaction success'
            ]);
        }

        // pesan error jika tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function getTransaction(Request $request)
    {
        $transactions = Transaction::whereIn('user_id', [$request->user()->id])->get();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Transaction success',
            'data'    => TransactionResource::collection($transactions)
        ], 200);
    }

    public function getTransactionById($id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'status'  => 'not found',
                'code'    => 404,
                'message' => 'Transaction not found'
            ], 404);
        }

        if (Gate::denies('private', $transaction)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see your own Transaction.'
            ], 403);
        }

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get Transaction success',
            'data'    => new TransactionResource($transaction)
        ], 200);
    }

    public function createTransaction(TransactionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $existingTransaction = Transaction::where('user_id', $data['user_id'])
            ->where('wallet_id', $data['wallet_id'])
            ->where('category_id', $data['category_id'])
            ->where('description', $data['description'])
            ->first();

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

        $transaction = Transaction::create($data);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Create Transaction success',
            'data'    => new TransactionResource($transaction)
        ]);
    }

    public function updateTransaction(TransactionRequest $request, $id)
    {
        if (Gate::denies('private', Transaction::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Transaction.'
            ], 403);
        }

        $data = $request->validated();

        $Transaction = Transaction::find($id);
        if ($Transaction) {
            $Transaction->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update Transaction success',
                'data'    => new TransactionResource($Transaction)
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }


    public function deleteTransaction($id)
    {
        if (Gate::denies('private', Transaction::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own Transaction.'
            ], 403);
        }

        $Transaction = Transaction::find($id);
        if ($Transaction) {
            $Transaction->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete Transaction success'
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Transaction not found'
        ], 404);
    }
}

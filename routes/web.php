<?php

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
  Transaction::all()->map(function ($transaction) {
    if ($transaction->recurring == 1) {
      Transaction::create([
        'user_id'     => $transaction->user_id,
        'wallet_id'   => $transaction->wallet_id,
        'category_id' => $transaction->category_id,
        'amount'      => $transaction->amount,
        'description' => $transaction->description,
        'recurring'   => 2,
      ]);
    }
  });
});

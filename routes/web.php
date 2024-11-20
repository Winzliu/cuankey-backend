<?php

use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
  Recurring::all()->map(function ($recurring) {
    if ($recurring->is_active == true) {
      Transaction::create([
        'user_id'     => $recurring->user_id,
        'wallet_id'   => $recurring->wallet_id,
        'category_id' => $recurring->category_id,
        'amount'      => $recurring->amount,
        'description' => $recurring->description,
      ]);
    }
  });
});

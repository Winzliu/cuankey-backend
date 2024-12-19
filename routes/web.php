<?php

use App\Models\Category;
use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
  $category = Category::with('transaction')
    ->withSum('transaction as total_transaction', 'amount')->get();

  return $category;
});

<?php

use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
  return Carbon::create(date('Y'), date('m'), 1)->format('d F Y');
});

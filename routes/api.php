<?php

use App\Http\Controllers\AuthController;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Wallet;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

// middleware dengan autorisasi menggunakan laravel sanctum untuk memastikan akses yang sah
Route::group(['middleware' => 'auth:sanctum'], function () {
  // Authentication
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::group(['prefix' => 'user'], function () {
    Route::get('/', [AuthController::class, 'getUser']);
    Route::put('/', [AuthController::class, 'updateUser']);
    Route::delete('/', [AuthController::class, 'deleteUser']);
  });
  Route::put('/password-update', [AuthController::class, 'updateUserPassword']);

  // Category
  Route::group(['prefix' => 'category'], function () {
    Route::post('/', [App\Http\Controllers\CategoryController::class, 'createCategory']);
    Route::get('/', [App\Http\Controllers\CategoryController::class, 'getCategory']);
    Route::get('/{id}', [App\Http\Controllers\CategoryController::class, 'getCategoryById']);
    Route::put('/{id}', [App\Http\Controllers\CategoryController::class, 'updateCategory']);
    Route::delete('/{id}', [App\Http\Controllers\CategoryController::class, 'deleteCategory']);
  });

  // Wallets
  Route::group(['prefix' => 'wallet'], function () {
    Route::post('/', [App\Http\Controllers\WalletController::class, 'createWallet']);
    Route::get('/', [App\Http\Controllers\WalletController::class, 'getWallet']);
    Route::get('/total', [App\Http\Controllers\WalletController::class, 'getAllWalletTotalBalance']);
    Route::get('/{id}', [App\Http\Controllers\WalletController::class, 'getWalletById']);
    Route::put('/{id}', [App\Http\Controllers\WalletController::class, 'updateWallet']);
    Route::put('/switch/{id}', [App\Http\Controllers\WalletController::class, 'switchWallet']);
    Route::delete('/{id}', [App\Http\Controllers\WalletController::class, 'deleteWallet']);
  });

  //Transactions
  Route::group(['prefix' => 'transaction'], function () {
    Route::post('/', [App\Http\Controllers\TransactionController::class, 'createTransaction']);
    Route::get('/', [App\Http\Controllers\TransactionController::class, 'getTransaction']);
    Route::get('/monthly', [App\Http\Controllers\TransactionController::class, 'getTransactionPerMonth']);
    Route::get('/{id}', [App\Http\Controllers\TransactionController::class, 'getTransactionById']);
    Route::put('/{id}', [App\Http\Controllers\TransactionController::class, 'updateTransaction']);
    Route::delete('/{id}', [App\Http\Controllers\TransactionController::class, 'deleteTransaction']);
  });

  //Recurring
  Route::group(['prefix' => 'recurring'], function () {
    Route::post('/', [App\Http\Controllers\RecurringController::class, 'createRecurring']);
    Route::get('/', [App\Http\Controllers\RecurringController::class, 'getRecurring']);
    Route::get('/{id}', [App\Http\Controllers\RecurringController::class, 'getRecurringById']);
    Route::put('/{id}', [App\Http\Controllers\RecurringController::class, 'updateRecurring']);
    Route::delete('/{id}', [App\Http\Controllers\RecurringController::class, 'deleteRecurring']);
  });
});

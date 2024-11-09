<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
  // Authentication
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::group(['prefix' => 'user'], function () {
    Route::get('/', [AuthController::class, 'getUser']);
    Route::put('/', [AuthController::class, 'updateUser']);
    Route::delete('/', [AuthController::class, 'deleteUser']);
  });
});

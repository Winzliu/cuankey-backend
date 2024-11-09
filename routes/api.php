<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

Route::put('/user', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');

Route::delete('/user', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');
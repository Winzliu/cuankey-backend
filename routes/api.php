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

  // Category
  Route::group(['prefix' => 'category'], function () {
    Route::post('/', [App\Http\Controllers\CategoryController::class, 'createCategory']);
    Route::get('/', [App\Http\Controllers\CategoryController::class, 'getCategory']);
    Route::get('/{id}', [App\Http\Controllers\CategoryController::class, 'getCategoryById']);
    Route::put('/{id}', [App\Http\Controllers\CategoryController::class, 'updateCategory']);
    Route::delete('/{id}', [App\Http\Controllers\CategoryController::class, 'deleteCategory']);
  });
});

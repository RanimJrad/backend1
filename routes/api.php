<?php
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->get('users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::delete('users/{id}', [UserController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/user/update/{id}', [AuthController::class, 'updateAdmin']);
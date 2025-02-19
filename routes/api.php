<?php
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Mail\RecruiterAdded;
use Illuminate\Support\Facades\Mail;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->get('users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::delete('users/{id}', [UserController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/user/update/{id}', [AuthController::class, 'updateAdmin']);
Route::middleware('auth:sanctum')->put('users/archive/{id}', [UserController::class, 'archiveUser']);
Route::middleware('auth:sanctum')->get('users/archived', [UserController::class, 'getArchivedUsers']);
Route::put('users/updateRec/{id}', [AuthController::class, 'updateRec']);

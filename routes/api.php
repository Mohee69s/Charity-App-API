<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[AuthController::class,"login"]);
Route::post('/register',[RegisterController::class,"register"]);

Route::middleware(['auth:api'])->group(function () {

    Route::post('/wallet/create',[WalletController::class,'store']);
    Route::post('/wallet',[WalletController::class,'index']);
    Route::patch('/wallet/pay',[WalletController::class,'update']);
    Route::get('/wallet/transactions',[WalletTransactionsController::class,'index']);
    Route::get('/user',[AuthController::class,'destroy']);
    
});

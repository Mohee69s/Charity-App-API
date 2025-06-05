<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[AuthController::class,"login"]);
Route::post('/register',[RegisterController::class,"register"]);

Route::middleware(['auth:api'])->group(function () {

    Route::post('/wallet/ceate',[WalletController::class,'store']);
    Route::post('/wallet',[WalletController::class,'index']);
    Route::patch('/wallet/pay',[WalletController::class,'update']);
});

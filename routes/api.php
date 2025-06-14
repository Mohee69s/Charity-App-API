<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\InKindDonationsController;
use App\Http\Controllers\RecurringDonationsController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[AuthController::class,"login"]);
Route::post('/register',[RegisterController::class,"register"]);

Route::middleware(['auth:api'])->group(function () {

    Route::get('/logout',[AuthController::class,'destroy']);
   
    //Wallet Routes
    Route::post('/wallet/create',[WalletController::class,'store']);
    Route::post('/wallet',[WalletController::class,'index']);
    Route::patch('/wallet/pay',[WalletController::class,'update']);
    Route::get('/wallet/transactions',[WalletTransactionsController::class,'index']);

    //Campaigns Routes
    Route::get('/campaigns',[CampaignController::class,'index']);
    Route::get('/campaigns',[CampaignController::class,'camp']);

    //Donations Routes
    Route::get('/donate',[DonationController::class,'index']);
    Route::post('/donate',[DonationController::class,'store']);
    Route::post('/rec',[RecurringDonationsController::class,'store']);
    Route::get('/inkind',[InKindDonationsController::class,'index']);
    
    
});

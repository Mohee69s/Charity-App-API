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

Route::post('/login', [AuthController::class, "login"]);
Route::post('/register', [RegisterController::class, "register"]);

Route::get('test', function () {
    return response()->json([
        'message' => 'noice from nvim'
    ]);
});
Route::middleware(['auth:api'])->group(function () {

    Route::get('/logout', [AuthController::class, 'destroy']);

    //Wallet Routes
    Route::post('/wallet/create', [WalletController::class, 'store']);
    Route::post('/wallet', [WalletController::class, 'index']);
    Route::patch('/wallet/pay', [WalletController::class, 'update']);
    Route::get('/wallet/transactions', [WalletTransactionsController::class, 'index']);

    //Campaigns Routes
    Route::get('/campaigns/donations', [CampaignController::class, 'donation']);
    Route::get('/campaigns/volunteering',[CampaignController::class,'volunteer']);


    Route::get('/campaigns/{id}', [CampaignController::class, 'camp']);


    //Donations Routes
    Route::get('/donate', [DonationController::class, 'index']);
    Route::post('/donate', [DonationController::class, 'store']);

    // Recurring Donations
    Route::get('/recurring/{status}', [RecurringDonationsController::class, 'index']);
    Route::post('/recurring', [RecurringDonationsController::class, 'store']);
    Route::patch('/recurring', [RecurringDonationsController::class, 'destroy']);

    // In kind donations
    Route::get('/inkind/{status}', [InKindDonationsController::class, 'index']);
    Route::post('/inkind', [InKindDonationsController::class, 'store']);
    Route::post('/inkind/camp', [InKindDonationsController::class, 'storeforcamp']);
});

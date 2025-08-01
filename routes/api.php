<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\InKindDonationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringDonationsController;
use App\Http\Controllers\VolunteerApplicationsController;
use App\Http\Controllers\VolunteeringController;
use App\Http\Controllers\VolunteerOpportunitiesController;
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

    Route::post('/wallet/create', [WalletController::class, 'store']);
    Route::get('/logout', [AuthController::class, 'destroy']);
    Route::get('/campaigns/donations', [CampaignController::class, 'donation']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'camp']);
    Route::get('/profile', [ProfileController::class,'index']);
    Route::post('/profile/password', [ProfileController::class,'updatepassword']);

    
        Route::get('/wallet', [WalletController::class, 'index']);
        Route::patch('/wallet/pay', [WalletController::class, 'update']);
        Route::get('/wallet/transactions', [WalletTransactionsController::class, 'index']);
        Route::get('wallet/all', [WalletController::class, 'allwalletdata']);
        Route::post('campaigns/{id}/donate', [CampaignController::class, 'donate']);
        Route::get('/donate', [DonationController::class, 'index']);
        Route::post('/donate', [DonationController::class, 'store']);
        // Recurring Donations
        Route::get('/recurring', [RecurringDonationsController::class, 'index']);
        Route::post('/recurring', [RecurringDonationsController::class, 'store']);
        Route::patch('/recurring/{id}', [RecurringDonationsController::class, 'destroy']);

        // In kind donations
        Route::get('/inkind', [InKindDonationsController::class, 'index']);
        Route::post('/inkind/{id}', [InKindDonationsController::class, 'store']);
    

    Route::middleware(['auth', 'role:volunteer'])->group(function () {
        Route::get('/campaigns/volunteering', [CampaignController::class, 'volunteer']);
        Route::get('/opportunity/{id}', [VolunteerOpportunitiesController::class, 'index']);
        Route::get('/volunteer/{id}', [CampaignController::class, 'camp']);


        Route::get('/volunteerlog', [VolunteeringController::class, 'index']);
        Route::patch('/volunteeringcancel', [VolunteeringController::class, 'cancelVol']);
        Route::post('volunteer/{id}', [VolunteeringController::class, 'store']);

        Route::post('/apply',[VolunteerApplicationsController::class,'store']);
    });

    //Voulunteering routes
});

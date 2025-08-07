<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EducationalRequestController;
use App\Http\Controllers\FoodRequestController;
use App\Http\Controllers\InKindDonationsController;
use App\Http\Controllers\MedicalRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringDonationsController;
use App\Http\Controllers\VolunteerApplicationsController;
use App\Http\Controllers\VolunteeringController;
use App\Http\Controllers\VolunteerOpportunitiesController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTransactionsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, "login"]);
Route::post('/register', [RegisterController::class, "register"]);

Route::get('test', function () {
    return response()->json([
        'message' => 'noice from nvim'
    ]);
});
Route::middleware(['auth:api'])->group(function () {

    /** -------------------------------
     *  Wallet and Donation Routes
     *  ------------------------------- */
    Route::middleware(['role:!beneficiary'])->group(function () {
        Route::post('/wallet/create', [WalletController::class, 'store']);
    });
    Route::middleware(['role:donator,!beneficiary'])->group(function () {
        // Wallet
        Route::patch('/wallet/pay', [WalletController::class, 'update']);
        Route::get('/wallet', [WalletController::class, 'index']);
        Route::get('/wallet/transactions', [WalletTransactionsController::class, 'index']);
        Route::get('/wallet/all', [WalletController::class, 'allwalletdata']);
        // One-time Donations
        Route::get('/donate', [DonationController::class, 'index']);
        Route::post('/donate', [DonationController::class, 'store']);
        // Campaign Donations
        Route::get('/campaigns/donations', [CampaignController::class, 'donation']);
        Route::get('/campaigns/{id}', [CampaignController::class, 'camp']);
        Route::post('/campaigns/{id}/donate', [CampaignController::class, 'donateRoute::g']);
        Route::get('/campaigns-types', [CampaignController::class, 'typesOfCampaigns']);
        // Recurring Donations
        Route::get('/recurring', [RecurringDonationsController::class, 'index']);
        Route::post('/recurring', [RecurringDonationsController::class, 'store']);
        Route::patch('/recurring/{id}', [RecurringDonationsController::class, 'destroy']);
    
        // In-Kind Donations
        Route::get('/inkind', [InKindDonationsController::class, 'index']);
        Route::post('/inkind/{id}', [InKindDonationsController::class, 'store']);
    });





    /** -------------------------------
     *  Volunteering Routes
     *  ------------------------------- */

    Route::post('/volunteer-apply', [VolunteerApplicationsController::class, 'store']);
    Route::get('/get-volunteering-status', [VolunteerApplicationsController::class, 'status']);
    Route::middleware(['role:volunteer,!beneficiary'])->group(function () {
            // Volunteering Opportunities & Campaigns
            Route::get('/campaigns/volunteering', [CampaignController::class, 'volunteer']);
            Route::get('/opportunity/{id}', [VolunteerOpportunitiesController::class, 'index']);
            Route::get('/volunteer/{id}', [CampaignController::class, 'camp']);
        
            // Volunteer Applications
        
            // Volunteering Actions
            Route::get('/volunteerlog', [VolunteeringController::class, 'index']);
            Route::patch('/volunteeringcancel', [VolunteeringController::class, 'cancelVol']);
            Route::post('/volunteer/{id}', [VolunteeringController::class, 'store']);

    });


    /** -------------------------------
     *  Forms and Applications
     *  ------------------------------- */

    Route::middleware(['role:beneficiary,!donator,!volunteer'])->group(function () {
        // Educational Assistance
        Route::post('/educational-request', [EducationalRequestController::class, 'store']);
        Route::get('/educational-form', [EducationalRequestController::class, 'index']);
    
        // Food Assistance
        Route::post('/food-request', [FoodRequestController::class, 'store']);
        Route::get('/food-form', [FoodRequestController::class, 'index']);
    
        // Medical Assistance
        Route::post('/medical-request', [MedicalRequestController::class, 'store']);
        Route::get('/medical-form', [MedicalRequestController::class, 'index']);
    });    

    /** -------------------------------
     *  Profile / Authentication
     *  ------------------------------- */

    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile/password', [ProfileController::class, 'updatepassword']);
    Route::get('/forget-password', [ProfileController::class, 'ForgetPassword']);
    Route::get('/logout', [AuthController::class, 'destroy']);
});

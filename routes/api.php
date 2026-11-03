<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CreatorController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\NegotiationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\AdminExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['data' => ['status' => 'ok']]));

    Route::post('/register/brand', [AuthController::class, 'registerBrand'])->middleware('throttle:10,1');
    Route::post('/register/creator', [AuthController::class, 'registerCreator'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::patch('/profile', [ProfileController::class, 'update']);

        Route::middleware('role:brand,admin')->group(function () {
            Route::get('/creators', [CreatorController::class, 'index']);
            Route::get('/creators/{slug}', [CreatorController::class, 'show']);
            Route::get('/brand/campaigns', [CampaignController::class, 'index']);
            Route::post('/brand/campaigns', [CampaignController::class, 'store']);
            Route::get('/brand/campaigns/{campaign}', [CampaignController::class, 'show']);
            Route::patch('/brand/campaigns/{campaign}', [CampaignController::class, 'update']);
            Route::post('/brand/campaigns/{campaign}/invitations', [InvitationController::class, 'store']);
            Route::get('/brand/negotiations', [NegotiationController::class, 'brandIndex']);
            Route::post('/brand/negotiations/{invitation}/offers', [NegotiationController::class, 'brandOffer']);
            Route::post('/brand/negotiations/{invitation}/respond', [NegotiationController::class, 'brandRespond']);
        });

        Route::middleware('role:creator,admin')->group(function () {
            Route::get('/creator/invitations', [InvitationController::class, 'creatorIndex']);
            Route::post('/creator/invitations/{invitation}/respond', [InvitationController::class, 'creatorRespond']);
            Route::post('/creator/invitations/{invitation}/offers', [NegotiationController::class, 'creatorOffer']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/export/leads', [AdminExportController::class, 'leads']);
            Route::get('/export/creators', [AdminExportController::class, 'creators']);
        });
    });
});

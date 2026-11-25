<?php

use App\Http\Controllers\Api\BikeApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/bikes', [BikeApiController::class, 'index']);
    Route::get('/bikes/{bike}', [BikeApiController::class, 'show']);
    Route::get('/rentals/active', [BikeApiController::class, 'activeRentals']);
});

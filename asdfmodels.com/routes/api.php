<?php

use App\Http\Controllers\Api\LocationLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')
    ->get('/locations', LocationLookupController::class)
    ->name('api.locations.index');



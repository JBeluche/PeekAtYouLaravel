<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Declared before the resource so it is not captured by /calendars/{calendar}.
    Route::get('/calendars/entries', [CalendarController::class, 'withEntries']);

    Route::apiResource('calendars', CalendarController::class);
    Route::apiResource('entries', EntryController::class);
    Route::apiResource('labels', LabelController::class);

    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);
});

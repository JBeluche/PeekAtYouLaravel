<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarsController;
use App\Http\Controllers\CalendarDatesController;
use App\Http\Controllers\ColorAssociationsController;
use Illuminate\Support\Facades\Route;

//Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('calendars', CalendarsController::class)->middleware('auth:sanctum');
Route::get('/calendars/{calendar}/dates', [CalendarsController::class, 'datesByCalendar'])->middleware('auth:sanctum');

Route::apiResource('calendar_dates', CalendarDatesController::class)->middleware('auth:sanctum');

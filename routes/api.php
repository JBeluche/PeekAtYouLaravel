<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarDateController;
use App\Http\Controllers\ColorAssociationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('calendar', CalendarController::class)->middleware('auth:sanctum');
Route::apiResource('color_association', ColorAssociationController::class)->middleware('auth:sanctum');
Route::apiResource('calendar_date', CalendarDateController::class)->middleware('auth:sanctum');
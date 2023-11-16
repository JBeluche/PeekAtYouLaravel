<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarsController;
use App\Http\Controllers\ColorAssociationsController;
use App\Http\Controllers\ColorAssociationDatesController;
use App\Http\Controllers\ColorsController;
use App\Http\Controllers\CalendarDatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'cors'])->get('/user', function (Request $request) {
    return $request->user();
});


//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

//Private, users only
Route::group(['middleware' => ['auth:sanctum', 'abilities:user']], function () {

    //Colors
    Route::get('/color', [ColorsController::class, 'index']);

    //Calendars
    Route::apiResource('/calendar', CalendarsController::class);

    //Dates
    Route::apiResource('/calendar_date', CalendarDatesController::class);
    Route::get('/calendar_date/calendar/{calendar}', [CalendarDatesController::class, 'datesByCalendar']);

    //Color Associations
    Route::post('/color_association/{calendar}', [ColorAssociationsController::class, 'storeMany']);
    Route::get('/color_association/{calendar}', [ColorAssociationsController::class, 'showByCalendar']);
    Route::patch('/color_association/{calendar}', [ColorAssociationsController::class, 'editMany']);
    Route::delete('/color_association', [ColorAssociationsController::class, 'destroyMany']);

    //Color Associations Date
    Route::post('/color_association_date/{date}', [ColorAssociationDatesController::class, 'storeMany']);
    Route::get('/color_association_date/{date}', [ColorAssociationDatesController::class, 'showByDate']);
    Route::patch('/color_association_date/{date}', [ColorAssociationDatesController::class, 'updateMany']);
    Route::delete('/color_association_date', [ColorAssociationDatesController::class, 'destroyMany']);

    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

//Private, admin only
Route::group(['middleware' => ['auth:sanctum', 'abilities:server']], function () {
    //Colors
    Route::apiResource('/color', ColorsController::class);
    Route::post('/colors', [ColorsController::class, 'storeMany']);
    Route::patch('/colors', [ColorsController::class, 'updateMany']);
});

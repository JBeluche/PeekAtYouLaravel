<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarsController;
use App\Http\Controllers\ColorAssociationsController;
use App\Http\Controllers\ColorAssociationDatesController;
use App\Http\Controllers\ColorsController;
use App\Http\Controllers\DatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'cors'])->get('/user', function (Request $request) {
    return $request->user();
});


//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


//Private
Route::group(['middleware' => ['auth:sanctum']], function () {

    //Calendars
    Route::get('/calendar/dates/{calendarId}', [CalendarsController::class, 'calendar_dates']);
    Route::apiResource('/calendar', CalendarsController::class);

    //Dates
    Route::apiResource('/date', DatesController::class);

    
    //Colors
    Route::apiResource('/color', ColorsController::class);
    Route::post('/color/many', [ColorsController::class, 'storeMany']);
    
    //Color Associations
    Route::post('/color_association', [ColorAssociationsController::class, 'storeMany']);
    Route::get('/color_association/{calendar_id}', [ColorAssociationsController::class, 'showByCalendar']);
    Route::patch('/color_association', [ColorAssociationsController::class, 'editMany']);
    Route::delete('/color_association', [ColorAssociationsController::class, 'destroyMany']);

    //Color Associations Date
    Route::post('/color_association_date', [ColorAssociationDatesController::class, 'storeMany']);
    Route::get('/color_association_date/{calendar_id}', [ColorAssociationDatesController::class, 'showByDate']);
    Route::patch('/color_association_date', [ColorAssociationDatesController::class, 'updateMany']);
    Route::delete('/color_association_date', [ColorAssociationDatesController::class, 'destroyMany']);


    
    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

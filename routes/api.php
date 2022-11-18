<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarsController;
use App\Http\Controllers\ColorsController;
use App\Http\Controllers\DatesController;
use App\Http\Controllers\PalettesController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


//Private
Route::group(['middleware' => ['auth:sanctum']], function () {

    //Calendars
    Route::get('/calendar/dates/{calendarId}', [CalendarsController::class, 'calendar_dates']);
    Route::resource('/calendar', CalendarsController::class);
    
    //Palettes
    Route::resource('/color', ColorsController::class);
    
    //Colors
    Route::resource('/palette', PalettesController::class);
    
    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

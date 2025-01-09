<?php

namespace App\Http\Controllers;

use App\Models\CalendarDate;
use App\Http\Requests\StoreCalendarDateRequest;
use App\Http\Requests\UpdateCalendarDateRequest;

class CalendarDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarDateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarDate $calendarDate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarDateRequest $request, CalendarDate $calendarDate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarDate $calendarDate)
    {
        //
    }
}

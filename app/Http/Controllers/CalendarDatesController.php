<?php

namespace App\Http\Controllers;

use App\Models\CalendarDate;
use App\Http\Requests\StoreCalendarDateRequest;
use App\Http\Requests\UpdateCalendarDateRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CalendarDateResource;
use App\Traits\HttpResponses;

class CalendarDatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use HttpResponses;



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarDateRequest $request)
    {
        $date = CalendarDate::create([
            'displayed_note' => $request->displayed_note,
            'symbol' => $request->symbol,
            'date' => $request->date,
            'user_id' => Auth::user()->id,
            'calendar_id' => $request->calendar_id,
            'color_association_id' => $request->color_association_id,
        ]);

        return new CalendarDateResource($date);
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarDate $calendarDate)
    {
        return $this->isNotAuthorized($calendarDate->calendar) ? $this->isNotAuthorized($calendarDate->calendar) : new CalendarDateResource($calendarDate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarDateRequest $request, CalendarDate $calendarDate)
    {
        $calendarDate->update([
            'symbol' => $request->symbol,
            'displayed_note' => $request->displayed_note,
            'color_association_id' => $request->color_association_id,
        ]);

        return new CalendarDateResource($calendarDate->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarDate $calendarDate)
    {
        return $this->isNotAuthorized($calendarDate->calendar) ? $this->isNotAuthorized($calendarDate->calendar) : $calendarDate->delete();
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
}

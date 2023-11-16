<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarDatesRequest;
use App\Http\Requests\UpdateCalendarDateRequest;
use App\Http\Resources\CalendarDatesResource;
use App\Models\Calendar;
use App\Models\CalendarDate;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CalendarDatesController extends Controller
{
    use HttpResponses;

    public function datesByCalendar(Calendar $calendar)
    {
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        $month = request()->query('month');
        $year = request()->query('year');

        if(strlen($month) > 2 || strlen($year) > 4){
            return 'Sorry sir, cant do that';
        }

        if(isset($month) && isset($year)){
            $calendarDates = CalendarDate::where('calendar_id', '=', $calendar->id)
                ->whereYear('date', '=', $year)
                ->whereMonth('date', '=', $month)
                ->get();

                return  CalendarDatesResource::collection($calendarDates);
        }

        $calendarDates = CalendarDate::where('calendar_id', '=', $calendar->id)
                ->whereYear('date', '=', date("Y"))
                ->whereMonth('date', '=', date("m"))
                ->get();

        return $this->success(
            CalendarDatesResource::collection($calendarDates),
            'We did not recieve period query succesfully, so I took everything from the current year and month.',
        );
    }


    public function store(StoreCalendarDatesRequest $request)
    {
        $date = CalendarDate::create([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
            'user_id' => Auth::user()->id,
            'calendar_id' => $request->calendar_id,
            'date' => $request->date,
            'color_association_id' => $request->color_association_id,
        ]);

        return new CalendarDatesResource($date);
    }

    public function show(CalendarDate $calendarDate)
    {
        return $this->isNotAuthorized($calendarDate->calendar) ? $this->isNotAuthorized($calendarDate->calendar) : new CalendarDatesResource($calendarDate);
    }

    public function update(UpdateCalendarDateRequest $request, CalendarDate $calendarDate)
    {
        $calendarDate->update([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
            'color_association_id' => $request->color_association_id,
        ]);

        return new CalendarDatesResource($calendarDate->fresh());
    }

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

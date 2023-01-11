<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatesRequest;
use App\Http\Requests\UpdateDatesRequest;
use App\Http\Resources\DatesResource;
use App\Models\Calendar;
use App\Models\Date;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class DatesController extends Controller
{
    use HttpResponses;

    public function calendar_dates($calendarId)
    {
        $this->isNotAuthorized(Calendar::where('id', $calendarId)->first());

        return DatesResource::collection(
            Date::where('calendar_id', $calendarId)->get()
        );
    }

    public function store(StoreDatesRequest $request)
    {

        //Check if calendar id valid, and from the same user
        $calendar = Calendar::where('id', $request->calendar_id)->first();
        if(!$calendar){
            return $this->error('', 'Wrong calendar id', 409);
        }

        if (Auth::user()->id !== $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        //Check if date for this calendar is already exixsting
        if (Date::where('date', $request->date)->where('calendar_id', $request->calendar_id)->first() != null) {
            return $this->error('', 'This date exists, and is already associated with a calendar', 409);
        }

        $request->validated($request->all());

        //Create new calendar
        $date = Date::create([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
            'user_id' => Auth::user()->id,
            'calendar_id' => $request->calendar_id,
            'date' => $request->date,
        ]);

        return new DatesResource($date);
    }

    public function show(Date $date)
    {
        if (Auth::user()->id !== $date->calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        return new DatesResource($date);
    }

    public function update(UpdateDatesRequest $request, Date $date)
    {

        if (Auth::user()->id != $date->calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        $request->validated($request->all());

        $date->update([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
        ]);

        return new DatesResource($date->fresh());
    }

    public function destroy(Date $date)
    {
        return $this->isNotAuthorized($date->calendar) ? $this->isNotAuthorized($date->calendar) : $date->delete();
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
    
}

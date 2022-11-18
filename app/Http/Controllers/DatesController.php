<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatesRequest;
use App\Http\Requests\UpdateDatesRequest;
use App\Http\Resources\DatesResource;
use App\Models\Calendar;
use App\Models\Date;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $calendar = Calendar::where('id', $request->calendar_id)->first();

        if (Auth::user()->id !== $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        if (
            DB::table('calendar_color')
            ->where('color_id', $request->color_id)
            ->where('calendar_id', $request->calendar_id)
            ->first() === null
        ) {
            return $this->error('', 'The relationship between the calendar and color does not exist. :(', 409);
        }

        if (Date::where('date', $request->date)->where('calendar_id', $request->calendar_id)->first() != null) {
            return $this->error('', 'This date exists, and is already associated with a calendar', 409);
        }

        $request->validated($request->all());

        //Create new calendar
        $date = Date::create([
            'info' => $request->info,
            'user_id' => Auth::user()->id,
            'calendar_id' => $request->calendar_id,
            'date' => $request->date,
            'color_id' => $request->color_id,
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

        $foundColor = false;

        foreach ($date->calendar->colors as $color) {
            if ($date->color_id != $color->id) {
                $foundColor = true;
            }
        }

        if (!$foundColor) {
            return $this->error('', 'The color is not associated with his calendar. What are you doing?', 403);
        }

        $date->update([
            'info' => $request->info,
            'color_id' => $request->color_id,
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatesRequest;
use App\Http\Requests\UpdateDateRequest;
use App\Http\Resources\DatesResource;
use App\Models\Calendar;
use App\Models\Date;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class DatesController extends Controller
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
            $dates = Date::where('calendar_id', '=', $calendar->id)
                ->whereYear('date', '=', $year)
                ->whereMonth('date', '=', $month)
                ->get();

                return  DatesResource::collection($dates);
        }

        $dates = Date::where('calendar_id', '=', $calendar->id)
                ->whereYear('date', '=', date("Y"))
                ->whereMonth('date', '=', date("m"))
                ->get();

        return $this->success(
            DatesResource::collection($dates),
            'We did not recieve period query succesfully, so I took everything from the current year and month.',
        );
    }


    public function store(StoreDatesRequest $request)
    {
        $date = Date::create([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
            'user_id' => Auth::user()->id,
            'calendar_id' => $request->calendar_id,
            'date' => $request->date,
            'extra_value' => $request->extra_value,
            'color_association_id' => $request->color_association_id,
        ]);

        return new DatesResource($date);
    }

    public function show(Date $date)
    {
        return $this->isNotAuthorized($date->calendar) ? $this->isNotAuthorized($date->calendar) : new DatesResource($date);
    }

    public function update(UpdateDateRequest $request, Date $date)
    {
        $date->update([
            'long_note' => $request->long_note,
            'displayed_note' => $request->displayed_note,
            'extra_value' => $request->extra_value,
            'color_association_id' => $request->color_association_id,
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

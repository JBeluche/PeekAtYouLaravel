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
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : DatesResource::collection($calendar->dates);
    }

    public function store(StoreDatesRequest $request)
    {
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
        return $this->isNotAuthorized($date->calendar) ? $this->isNotAuthorized($date->calendar) : new DatesResource($date);
    }

    public function update(UpdateDateRequest $request, Date $date)
    {
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

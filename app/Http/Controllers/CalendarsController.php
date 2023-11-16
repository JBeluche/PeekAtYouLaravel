<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarsRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CalendarsController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $month = request()->query('month');
        $year = request()->query('year');

        if(strlen($month) > 2 || strlen($year) > 4){
            return 'Sorry sir, cant do that';
        }
        
        return CalendarResource::collection(
            Calendar::where('user_id', Auth::user()->id)->get()
        );
    }

    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarResource($calendar);
    }

    public function store(StoreCalendarsRequest $request)
    {
        $calendar = Calendar::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
        ]);

        return new CalendarResource($calendar);
    }

    public function update(Calendar $calendar, UpdateCalendarRequest $request,)
    {
        $calendar->update([
            'name' => $request->name,
        ]);

        return new CalendarResource($calendar->fresh());
    }

    public function destroy(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : $calendar->delete();
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id !== $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
}

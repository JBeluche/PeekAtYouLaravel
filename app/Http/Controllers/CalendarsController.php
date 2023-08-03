<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarsRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\OneCalendarResource;
use App\Http\Resources\MultipleCalendarsResource;
use App\Models\Calendar;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CalendarsController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return MultipleCalendarsResource::collection(
            Calendar::where('user_id', Auth::user()->id)->get()
        );
    }

    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new OneCalendarResource($calendar);
    }

    public function store(StoreCalendarsRequest $request)
    {
        $calendar = Calendar::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'is_bullet_calendar' => $request->is_bullet_calendar,
        ]);

        return new OneCalendarResource($calendar);
    }

    public function update(Calendar $calendar, UpdateCalendarRequest $request,)
    {
        $calendar->update([
            'name' => $request->name,
        ]);

        return new OneCalendarResource($calendar->fresh());
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

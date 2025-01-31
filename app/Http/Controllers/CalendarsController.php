<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarDateResource;
use App\Http\Resources\CalendarResource;
use App\Models\CalendarDate;
use App\Models\ColorAssociation;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CalendarsController extends Controller
{
    use HttpResponses;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Get all calendars and there dates
        return CalendarResource::collection(
            Calendar::where('user_id', Auth::user()->id)->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarRequest $request)
    {
        $calendar = Calendar::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
        ]);

        // Add associated dates
        $calendar->colorAssociations()->createMany($request->color_associations);

        return new CalendarResource($calendar);
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarResource($calendar);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar)
    {
        $calendar->update([
            'name' => $request->name,
        ]);

        //Delete old associations
        $calendar->colorAssociations()->whereNotIn('id', collect($request->color_associations)
            ->pluck('id')
            ->filter(fn($id) => $id > 0))->delete();

        foreach ($request->color_associations as $association) {
            // Add calendar_id to the association if not already present
            $association['calendar_id'] = $calendar->id;

            ColorAssociation::updateOrCreate(
                ['id' => $association['id'] ?? null], // Existing association
                $association // The updated/created association data with calendar_id
            );
        }

        return new CalendarResource($calendar->load('colorAssociations'));
    }

    /**
     * Remove the specified resource from storage.
     */
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

    public function datesByCalendar(Calendar $calendar)
    {

        //Check if calendar
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }

        $month = request()->query('month');
        $year = request()->query('year');

        if (isset($month) && isset($year)) {
            $calendarDates = CalendarDate::where('calendar_id', '=', $calendar->id)
                ->whereYear('date', '=', $year)
                ->whereMonth('date', '=', $month)
                ->get();

            return  CalendarDateResource::collection($calendarDates);
        }

        $calendarDates = CalendarDate::where('calendar_id', '=', $calendar->id)
            ->whereYear('date', '=', date("Y"))
            ->whereMonth('date', '=', date("m"))
            ->get();

        return $this->success(
            CalendarDateResource::collection($calendarDates),
            'We did not recieve period query succesfully, so I took everything from the current year and month.',
        );
    }

}

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
            Calendar::where('user_id', Auth::user()->id)->get()->load('colorAssociations')

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

        return new CalendarResource($calendar->load('colorAssociations'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarResource($calendar->load('colorAssociations'));
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

    public function userCalendarsWithDates()
    {
        $month = request()->query('month', date("m"));
        $year = request()->query('year', date("Y"));
        $userId = Auth::id(); // Get authenticated user ID

        // Fetch only user's calendars with filtered dates
        $calendars = Calendar::with([
            'calendarDates' => function ($query) use ($month, $year) {
                $query->whereYear('date', $year)->whereMonth('date', $month);
            }
        ])->where('user_id', $userId)
          ->whereHas('calendarDates', function ($query) use ($month, $year) {
              $query->whereYear('date', $year)->whereMonth('date', $month);
          })->get();

        return $this->success(
            'Fetched user calendars with filtered dates',
            CalendarResource::collection($calendars)
        );
    }
}

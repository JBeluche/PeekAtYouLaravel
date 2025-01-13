<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CalendarController extends Controller
{
    use HttpResponses;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        try {
            // This ensures $calendar is passed correctly by route-model binding
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'The requested calendar could not be found.'
            ], 404);
        }

        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarResource($calendar);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarRequest $request, Calendar $calendar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendar $calendar)
    {
        //
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id !== $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
}

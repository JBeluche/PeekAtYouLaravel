<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $calendars = $request->user()->calendars()->with('labels')->get();

        return $this->success(null, CalendarResource::collection($calendars));
    }

    public function store(StoreCalendarRequest $request): JsonResponse
    {
        $calendar = DB::transaction(function () use ($request) {
            $calendar = $request->user()->calendars()->create(
                $request->safe()->only('name')
            );

            if ($labels = $request->validated('labels')) {
                $calendar->labels()->createMany($labels);
            }

            return $calendar;
        });

        return $this->success('Calendar created', new CalendarResource($calendar->load('labels')), 201);
    }

    public function show(Calendar $calendar): JsonResponse
    {
        $this->authorize('view', $calendar);

        return $this->success(null, new CalendarResource($calendar->load('labels')));
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar): JsonResponse
    {
        $this->authorize('update', $calendar);

        $calendar->update($request->safe()->only('name'));

        return $this->success('Calendar updated', new CalendarResource($calendar->load('labels')));
    }

    public function destroy(Calendar $calendar): JsonResponse
    {
        $this->authorize('delete', $calendar);

        $calendar->delete();

        return $this->success('Calendar deleted');
    }

    /**
     * Every calendar the caller owns, with that month's entries and the colour
     * legend attached. This is the endpoint the client hydrates a month from.
     */
    public function withEntries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'integer', 'between:1970,9999'],
        ]);

        $month = $validated['month'] ?? (int) date('m');
        $year = $validated['year'] ?? (int) date('Y');

        $calendars = $request->user()->calendars()
            ->with([
                'labels',
                'entries' => fn ($query) => $query
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->orderBy('date'),
            ])
            ->get();

        return $this->success(
            'Fetched user calendars with filtered entries',
            CalendarResource::collection($calendars)
        );
    }
}

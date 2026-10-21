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
        $request->validate(['since' => ['sometimes', 'date']]);

        $calendars = $request->user()->calendars()
            ->when($request->date('since'), fn ($query, $since) => $query->changedSince($since))
            ->with('labels')
            ->get();

        return $this->success(null, CalendarResource::collection($calendars));
    }

    /**
     * A retry with the same client_uuid returns the calendar already made.
     */
    public function store(StoreCalendarRequest $request): JsonResponse
    {
        $calendar = DB::transaction(function () use ($request) {
            $calendar = $request->user()->calendars()->withTrashed()->createOrFirst(
                ['client_uuid' => $request->validated('client_uuid')],
                ['name' => $request->validated('name')],
            );

            if ($calendar->wasRecentlyCreated && $labels = $request->validated('labels')) {
                $calendar->labels()->createMany($labels);
            }

            return $calendar;
        });

        return $this->success(
            $calendar->wasRecentlyCreated ? 'Calendar created' : 'Calendar already exists',
            new CalendarResource($calendar->load('labels')),
            $calendar->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function show(Calendar $calendar): JsonResponse
    {
        $this->authorize('view', $calendar);

        return $this->success(null, new CalendarResource($calendar->load('labels')));
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar): JsonResponse
    {
        $calendar->update($request->safe()->only('name'));

        return $this->success('Calendar updated', new CalendarResource($calendar->load('labels')));
    }

    public function destroy(Calendar $calendar): JsonResponse
    {
        $this->authorize('delete', $calendar);

        // Soft-deletes the calendar and, through the model, its labels and entries.
        DB::transaction(fn () => $calendar->delete());

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

        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);

        $calendars = $request->user()->calendars()
            ->with([
                'labels',
                'entries' => fn ($query) => $query->datedIn($year, $month)->orderBy('date'),
            ])
            ->get();

        return $this->success(
            'Fetched user calendars with filtered entries',
            CalendarResource::collection($calendars)
        );
    }
}

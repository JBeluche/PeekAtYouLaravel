<?php

namespace App\Http\Controllers\Api;

use App\Enums\EntrySymbol;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EntryController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calendar_id' => ['sometimes', 'integer'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            // A month on its own would match that month in every year.
            'year' => ['required_with:month', 'integer', 'between:1970,9999'],
            'since' => ['sometimes', 'date'],
        ]);

        $since = $request->date('since');

        // A sync must also see entries of deleted calendars, which were deleted with them.
        $calendarIds = $request->user()->calendars()
            ->when($since, fn ($query) => $query->withTrashed())
            ->select('id');

        $entries = Entry::query()
            ->whereIn('calendar_id', $calendarIds)
            ->when($since, fn ($query) => $query->changedSince($since))
            ->when(
                isset($validated['calendar_id']),
                fn ($query) => $query->where('calendar_id', $validated['calendar_id'])
            )
            ->when(
                isset($validated['year']),
                fn ($query) => $query->datedIn(
                    (int) $validated['year'],
                    isset($validated['month']) ? (int) $validated['month'] : null,
                )
            )
            ->orderBy('date')
            ->get();

        return $this->success(null, EntryResource::collection($entries));
    }

    /**
     * Writes the note for a day: creates it, or updates the one already there,
     * so a retried request or two devices writing the same day never duplicate
     * it. A deleted day comes back blank before the new values are applied.
     */
    public function store(StoreEntryRequest $request): JsonResponse
    {
        $values = $request->validated();

        $entry = Entry::withTrashed()->createOrFirst(Arr::only($values, ['calendar_id', 'date']), $values);

        if (! $entry->wasRecentlyCreated) {
            if ($entry->trashed()) {
                $entry->forceFill([
                    'deleted_at' => null,
                    'text' => null,
                    'pen_color' => null,
                    'symbol' => EntrySymbol::None,
                    'mark_color' => null,
                ]);
            }

            $entry->update($values);
        }

        return $this->success(
            $entry->wasRecentlyCreated ? 'Entry created' : 'Entry saved',
            new EntryResource($entry),
            $entry->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function show(Entry $entry): JsonResponse
    {
        $this->authorize('view', $entry);

        return $this->success(null, new EntryResource($entry));
    }

    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        // calendar_id and date are deliberately not updatable; an entry is its calendar's note for one day.
        $entry->update($request->validated());

        return $this->success('Entry updated', new EntryResource($entry->fresh()));
    }

    public function destroy(Entry $entry): JsonResponse
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        return $this->success('Entry deleted');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calendar_id' => ['sometimes', 'integer'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'integer', 'between:1970,9999'],
        ]);

        $entries = Entry::query()
            ->whereIn('calendar_id', $request->user()->calendars()->select('id'))
            ->when(
                isset($validated['calendar_id']),
                fn ($query) => $query->where('calendar_id', $validated['calendar_id'])
            )
            ->when(
                isset($validated['year']),
                fn ($query) => $query->whereYear('date', $validated['year'])
            )
            ->when(
                isset($validated['month']),
                fn ($query) => $query->whereMonth('date', $validated['month'])
            )
            ->orderBy('date')
            ->get();

        return $this->success(null, EntryResource::collection($entries));
    }

    public function store(StoreEntryRequest $request): JsonResponse
    {
        $entry = Entry::create($request->validated());

        return $this->success('Entry created', new EntryResource($entry), 201);
    }

    public function show(Entry $entry): JsonResponse
    {
        $this->authorize('view', $entry);

        return $this->success(null, new EntryResource($entry));
    }

    public function update(UpdateEntryRequest $request, Entry $entry): JsonResponse
    {
        $this->authorize('update', $entry);

        // calendar_id is deliberately not updatable; an entry stays on its calendar.
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

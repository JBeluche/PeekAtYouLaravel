<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabelRequest;
use App\Http\Requests\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    use HttpResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calendar_id' => ['sometimes', 'integer'],
            'since' => ['sometimes', 'date'],
        ]);

        $since = $request->date('since');

        // A sync must also see labels of deleted calendars, which were deleted with them.
        $calendarIds = $request->user()->calendars()
            ->when($since, fn ($query) => $query->withTrashed())
            ->select('id');

        $labels = Label::query()
            ->whereIn('calendar_id', $calendarIds)
            ->when($since, fn ($query) => $query->changedSince($since))
            ->when(
                isset($validated['calendar_id']),
                fn ($query) => $query->where('calendar_id', $validated['calendar_id'])
            )
            ->get();

        return $this->success(null, LabelResource::collection($labels));
    }

    /**
     * A retry with the same client_uuid returns the label already made.
     */
    public function store(StoreLabelRequest $request): JsonResponse
    {
        $values = $request->validated();

        $label = Label::withTrashed()->createOrFirst(
            ['calendar_id' => $values['calendar_id'], 'client_uuid' => $values['client_uuid'] ?? null],
            $values,
        );

        return $this->success(
            $label->wasRecentlyCreated ? 'Label created' : 'Label already exists',
            new LabelResource($label),
            $label->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function show(Label $label): JsonResponse
    {
        $this->authorize('view', $label);

        return $this->success(null, new LabelResource($label));
    }

    public function update(UpdateLabelRequest $request, Label $label): JsonResponse
    {
        $label->update($request->validated());

        return $this->success('Label updated', new LabelResource($label->fresh()));
    }

    public function destroy(Label $label): JsonResponse
    {
        $this->authorize('delete', $label);

        $label->delete();

        return $this->success('Label deleted');
    }
}

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
        ]);

        $labels = Label::query()
            ->whereIn('calendar_id', $request->user()->calendars()->select('id'))
            ->when(
                isset($validated['calendar_id']),
                fn ($query) => $query->where('calendar_id', $validated['calendar_id'])
            )
            ->get();

        return $this->success(null, LabelResource::collection($labels));
    }

    public function store(StoreLabelRequest $request): JsonResponse
    {
        $label = Label::create($request->validated());

        return $this->success('Label created', new LabelResource($label), 201);
    }

    public function show(Label $label): JsonResponse
    {
        $this->authorize('view', $label);

        return $this->success(null, new LabelResource($label));
    }

    public function update(UpdateLabelRequest $request, Label $label): JsonResponse
    {
        $this->authorize('update', $label);

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

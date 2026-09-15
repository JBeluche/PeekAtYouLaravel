<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Calendar
 */
class CalendarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'entries' => EntryResource::collection($this->whenLoaded('entries')),
        ];
    }
}

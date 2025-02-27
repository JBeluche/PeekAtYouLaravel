<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color_associations' => ColorAssociationResource::collection($this->whenLoaded('colorAssociations')),
            'calendar_dates' => CalendarDateResource::collection($this->whenLoaded('calendarDates')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarDateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'long_note' => $this->long_note,
            'displayed_note' => $this->displayed_note,
            'extra_value' => $this->extra_value,
            'date' => $this->date,
            'color_association' => new ColorAssociationResource($this->colorAssociation),
        ];
    }
}

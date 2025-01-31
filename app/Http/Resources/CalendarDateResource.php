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
            'displayed_note' => $this->displayed_note,
            'symbol' => $this->symbol,
            'date' => $this->date,
            'color_association' => new ColorAssociationResource($this->colorAssociation),
        ];
    }
}

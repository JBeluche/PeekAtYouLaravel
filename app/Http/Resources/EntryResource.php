<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Entry
 */
class EntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'calendar_id' => $this->calendar_id,
            'date' => $this->date?->toDateString(),
            'text' => $this->text,
            'pen_color' => $this->pen_color,
            'symbol' => $this->symbol,
            'mark_color' => $this->mark_color,
        ];
    }
}

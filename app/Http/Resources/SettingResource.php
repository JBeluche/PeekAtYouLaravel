<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Setting
 */
class SettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'note_font_size' => $this->note_font_size,
            'note_font' => $this->note_font,
            'day_mark_style' => $this->day_mark_style,
        ];
    }
}

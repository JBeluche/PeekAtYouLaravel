<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Setting
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
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\DayMarkStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note_font_size' => ['sometimes', 'numeric', 'between:8,72', 'multiple_of:0.25'],
            'note_font' => ['sometimes', 'string', 'max:64'],
            'day_mark_style' => ['sometimes', Rule::enum(DayMarkStyle::class)],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Rules\HexColor;
use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
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
            'client_uuid' => ['sometimes', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'labels' => ['sometimes', 'array'],
            'labels.*.color' => ['required', new HexColor, 'distinct'],
            'labels.*.name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_array($labels = $this->input('labels'))) {
            $this->merge(['labels' => array_map(
                fn ($label) => is_array($label) && is_string($label['color'] ?? null)
                    ? ['color' => strtolower($label['color'])] + $label
                    : $label,
                $labels,
            )]);
        }
    }
}

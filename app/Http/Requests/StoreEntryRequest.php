<?php

namespace App\Http\Requests;

use App\Enums\EntrySymbol;
use App\Rules\HexColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntryRequest extends FormRequest
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
            // Scoped to the caller, so an entry cannot land on someone else's calendar.
            'calendar_id' => [
                'required',
                Rule::exists('calendars', 'id')
                    ->where('owner_id', $this->user()->id)
                    ->withoutTrashed(),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'text' => ['nullable', 'string'],
            'pen_color' => ['nullable', 'string', 'max:32'],
            'symbol' => ['sometimes', Rule::enum(EntrySymbol::class)],
            'mark_color' => ['nullable', new HexColor],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('mark_color'))) {
            $this->merge(['mark_color' => strtolower($this->input('mark_color'))]);
        }
    }
}

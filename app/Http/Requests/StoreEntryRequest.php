<?php

namespace App\Http\Requests;

use App\Enums\EntrySymbol;
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
                Rule::exists('calendars', 'id')->where('owner_id', $this->user()->id),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'text' => ['nullable', 'string'],
            'pen_color' => ['nullable', 'string', 'max:32'],
            'symbol' => ['sometimes', Rule::enum(EntrySymbol::class)],
            // Null clears the mark; anything else must already be in this calendar's legend.
            'mark_color' => [
                'nullable',
                'string',
                Rule::exists('labels', 'color')->where('calendar_id', $this->input('calendar_id')),
            ],
        ];
    }
}

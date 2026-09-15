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
            // Null leaves the day unmarked; otherwise the label must belong to
            // the same calendar the entry is being written to.
            'label_id' => [
                'nullable',
                Rule::exists('labels', 'id')->where('calendar_id', $this->input('calendar_id')),
            ],
        ];
    }
}

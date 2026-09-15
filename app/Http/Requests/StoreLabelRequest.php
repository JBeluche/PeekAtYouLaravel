<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabelRequest extends FormRequest
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
            // Scoped to the caller, so a label cannot be attached to someone else's calendar.
            'calendar_id' => [
                'required',
                Rule::exists('calendars', 'id')->where('owner_id', $this->user()->id),
            ],
            'color' => [
                'required',
                'string',
                'max:32',
                Rule::unique('labels', 'color')->where('calendar_id', $this->input('calendar_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}

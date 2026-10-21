<?php

namespace App\Http\Requests;

use App\Rules\HexColor;
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
        $uuid = $this->string('client_uuid')->toString() ?: null;

        return [
            // Scoped to the caller, so a label cannot be attached to someone else's calendar.
            'calendar_id' => [
                'required',
                Rule::exists('calendars', 'id')
                    ->where('owner_id', $this->user()->id)
                    ->withoutTrashed(),
            ],
            'client_uuid' => ['sometimes', 'uuid'],
            'color' => [
                'required',
                new HexColor,
                // Unique among live labels. A retry of this same create is not a clash.
                Rule::unique('labels', 'color')
                    ->where('calendar_id', $this->input('calendar_id'))
                    ->withoutTrashed()
                    ->when($uuid !== null, fn ($rule) => $rule->where(
                        fn ($query) => $query->whereNull('client_uuid')->orWhere('client_uuid', '!=', $uuid)
                    )),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('color'))) {
            $this->merge(['color' => strtolower($this->input('color'))]);
        }
    }
}

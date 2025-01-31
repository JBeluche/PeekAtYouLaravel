<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            [
                'name' => ['required', 'string', 'max:75'],
                'color_associations' => ['array', 'required'],
                'color_associations.*.association_text' => ['required', 'string', 'max:250'],
                'color_associations.*.hex_value' => ['required', 'string', 'regex:/^[0-9A-F]{6}$/i', function ($attribute, $value, $fail) {
                    // Check if the hex value already exists in the database for the given calendar
                    $calendarId = $this->input('calendar_id');
                    $existsInDb = \App\Models\ColorAssociation::where('calendar_id', $calendarId)
                        ->where('hex_value', $value)
                        ->exists();
                    if ($existsInDb) {
                        $fail("The hex value $value is already associated with this calendar.");
                    }

                    // Check for duplicates in the current request
                    $hexValues = collect($this->input('color_associations'))->pluck('hex_value')->toArray();
                    $duplicates = array_count_values($hexValues);
                    if ($duplicates[$value] > 1) {
                        $fail("The hex value $value appears more than once in the request.");
                    }
                }],
            ]
        );
    }
}

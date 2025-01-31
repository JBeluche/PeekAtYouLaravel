<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCalendarDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $calendar = Calendar::find(request()->calendar_id);

        if (!is_null($calendar) && Auth::user()->id !== $calendar->user_id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => ['nullable', 'string', 'max:42'],
            'displayed_note' => ['nullable', 'string', 'max:255'],
            'date' => [
                'required', 'date_format:Y-m-d',
            ],
            'calendar_id' => ['required', 'numeric', 'exists:calendars,id'],
            'color_association_id' => ['required', 'numeric',  'exists:color_associations,id'],
        ];
    }
}

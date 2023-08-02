<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDatesRequest extends FormRequest
{
    public function authorize()
    {
        $calendar = Calendar::find(request()->calendar_id);

        if (!is_null($calendar) && Auth::user()->id !== $calendar->user_id) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'long_note' => ['nullable', 'string', 'max:255'],
            'displayed_note' => ['nullable', 'string', 'max:42'],
            'date' => [
                'required', 'date_format:Y-m-d',
     
            ],
            'calendar_id' => ['required', 'numeric', 'exists:calendars,id'],
            'extra_value' => ['required', 'numeric', 'digits:1', 'exists:calendars,id'],
            'color_association_id' => ['required', 'numeric',  'exists:color_associations,id'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use App\Rules\CalendarIdCheck;
use App\Rules\CombinationExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreColorAssociationDateRequest extends FormRequest
{

    public function authorize()
    {
        $calendar = Calendar::find($this->route('date')->calendar_id);

        if (!is_null($calendar) && Auth::user()->id !== $calendar->user_id) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'color_association_dates' => ['required', 'array'],
            'color_association_dates.*.color_association_id' => [
                'required', 'integer', 'distinct',
                'exists:color_associations,id',
                new CalendarIdCheck,
                new CombinationExists('color_association_dates', 'store')
            ],
            'color_association_dates.*.extra_value' => ['required', 'integer', 'between:0,10'],

        ];
    }
}
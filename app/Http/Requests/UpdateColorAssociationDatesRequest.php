<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use App\Rules\CalendarIdCheck;
use App\Rules\CombinationExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateColorAssociationDatesRequest extends FormRequest
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
            'color_association_dates' => ['array', 'required'],
            'color_association_dates.*.id' => ['required', 'integer', 'exists:color_association_dates', 'distinct', new CombinationExists('color_association_dates', 'update')],
            'color_association_dates.*.color_association_id' => ['required', 'integer', 'exists:color_associations,id', 'distinct', new CalendarIdCheck,],
            'color_association_dates.*.extra_value' => ['required', 'integer', 'between:0,10'],
        ];
    }
}

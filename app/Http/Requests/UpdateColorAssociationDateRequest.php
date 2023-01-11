<?php

namespace App\Http\Requests;

use App\Rules\CalendarIdCheck;
use App\Rules\CalendarIdCheckOnUpdate;
use App\Rules\CheckAssociationDateDuplications;
use App\Rules\CheckDateId;
use App\Rules\DateAssociationExistOnUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateColorAssociationDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'date_id' => ['required', 'numeric', 'exists:dates,id'],
            'color_association_dates' => ['array', 'required'],
            'color_association_dates.*.id' => ['required', 'exists:color_association_dates', new CheckDateId, new CalendarIdCheck, new CheckAssociationDateDuplications  ],
            'associations.*.color_association_id' => ['required', 'numeric', 'distinct'],
            'associations.*.extra_value' => ['required', 'integer', 'between:0,10'],   
        ];
    }
}

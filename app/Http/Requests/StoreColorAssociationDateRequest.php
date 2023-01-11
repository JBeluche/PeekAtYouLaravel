<?php

namespace App\Http\Requests;

use App\Rules\CalendarIdCheck;
use App\Rules\DateColorAssociationExist;
use Illuminate\Foundation\Http\FormRequest;

class StoreColorAssociationDateRequest extends FormRequest
{
  
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_id' => ['required', 'numeric', 'exists:color_association_dates'],
            'associations.*.color_association_id' => ['required', 'numeric', 'distinct', new CalendarIdCheck, new DateColorAssociationExist],
            'associations.*.extra_value' => ['required', 'integer', 'between:0,10'],
            
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\ColorAssociationDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DestroyColorAssociationDatesRequest extends FormRequest
{
    public function authorize()
    {
        foreach (request()->color_association_date_ids as $id) {
            $foundModel = ColorAssociationDate::find($id);
            if (!is_null($foundModel) && $foundModel->colorAssociation->calendar->user_id != Auth::user()->id) {
                return false;
            }
        }

        return true;
    }

    public function rules()
    {
        return [

            'color_association_date_ids' => ['required', 'array'],
            'color_association_date_ids.*' => ['integer', 'required', 'distinct', 'exists:color_association_dates,id'],
        ];
    }
}

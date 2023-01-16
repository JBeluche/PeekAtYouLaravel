<?php

namespace App\Http\Requests;

use App\Models\ColorAssociation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DestroyColorAssociationsRequest extends FormRequest
{
    public function authorize()
    {
        foreach(request()->color_association_ids as $id){
            $foundModel = ColorAssociation::find($id);
            if(!is_null($foundModel) && $foundModel->calendar_id != Auth::user()->id){
                return false;
            }
        }

        return true;
    }

    public function rules()
    {
        return [
            'color_association_ids' => ['required', 'array'],
            'color_association_ids.*' => ['integer', 'required', 'distinct', 'exists:color_associations,id'],
        ];
    }
}

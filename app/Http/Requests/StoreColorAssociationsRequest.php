<?php

namespace App\Http\Requests;

use App\Rules\CombinationExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreColorAssociationsRequest extends FormRequest
{
    public function authorize()
    {
        if (Auth::user()->id !== $this->route('calendar')->user_id) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'color_associations' => ['array', 'required'],
            'color_associations.*.hex_value' => [
                'required', 'string', 'max:6'
            ],
            'color_associations.*.association_text' => ['required', 'string', 'max:250'],
        ];
    }
}

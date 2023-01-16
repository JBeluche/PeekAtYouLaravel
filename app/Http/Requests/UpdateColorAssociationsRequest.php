<?php

namespace App\Http\Requests;

use App\Rules\CombinationExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateColorAssociationsRequest extends FormRequest
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
            'color_associations' => ['required', 'array'],
            'color_associations.*.id' => ['required', 'integer', 'exists:color_associations,id', 'distinct', new CombinationExists('color_associations', 'update')],
            'color_associations.*.color_id' => ['required', 'integer', 'exists:colors,id', 'distinct'],
            'color_associations.*.association_text' => ['required', 'string', 'max:250'],
        ];
    }
}

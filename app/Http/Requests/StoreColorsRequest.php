<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'colors' => ['array', 'required'],
            'colors.*.hex_value' => ['required', 'string', 'max:10', 'distinct', 'unique:colors,hex_value'],
        ];
    }
}

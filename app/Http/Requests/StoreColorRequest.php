<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hex_value' => ['required', 'string', 'max:10', 'unique:colors,hex_value'],

        ];
    }
}

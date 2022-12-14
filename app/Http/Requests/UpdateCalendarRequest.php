<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
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
            'name' => [ 'string', 'between:1,75'],
            'colors' => ['array'],
            'colors.*.old_hex_value' =>  ['nullable','string', 'between:4,10'],
            'colors.*.new_hex_value' =>  ['required', 'string', 'between:4,10'],
        ];
    }
}

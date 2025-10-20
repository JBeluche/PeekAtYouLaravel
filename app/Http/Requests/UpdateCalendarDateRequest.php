<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => ['nullable', 'string', 'max:255'],
            'displayed_note' => ['nullable', 'string', 'max:255'],
            'color_association_id' => [
                'numeric',
                'required',
                //Either -1 (no color) or must exist in color_associations table
                function ($attribute, $value, $fail) {
                    if ($value != -1 && !\App\Models\ColorAssociation::where('id', $value)->exists()) {
                        $fail('The selected color association is invalid.');
                    }
                },
            ],
        ];
    }
}

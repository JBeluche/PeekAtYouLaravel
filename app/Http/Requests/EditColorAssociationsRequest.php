<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditColorAssociationsRequest extends FormRequest
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
            'items.*.id' => ['required', 'numeric'],
            'items.*.color_id' => ['required', 'numeric'],
            'items.*.association_text' => ['required', 'string', 'max:250'],
        ];
    }
}

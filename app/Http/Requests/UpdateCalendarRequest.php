<?php

namespace App\Http\Requests;

use App\Models\ColorAssociation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (Auth::user()->id !== $this->route('calendar')->user_id) {
            return false;
        }

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
            'name' => [ 'string', 'between:1,75', 'required'],
            'color_associations.*.id' => ['required', 'integer', 
            function ($attribute, $value, $fail) {
                if ($value !== -1 && !ColorAssociation::where('id', $value)->exists()) {
                    $fail("The selected {$attribute} is invalid.");
                }
            }],
            'color_associations.*.association_text' => ['required', 'string', 'max:250'],
            'color_associations.*.hex_value' => ['required', 'string', 'regex:/^[0-9A-F]{6}$/i','distinct'],
        ];
    }
}

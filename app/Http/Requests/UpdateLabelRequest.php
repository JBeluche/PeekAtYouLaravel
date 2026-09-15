<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'color' => [
                'sometimes',
                'required',
                'string',
                'max:32',
                Rule::unique('labels', 'color')
                    ->where('calendar_id', $this->route('label')->calendar_id)
                    ->ignore($this->route('label')->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}

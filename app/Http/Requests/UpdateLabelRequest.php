<?php

namespace App\Http\Requests;

use App\Rules\HexColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLabelRequest extends FormRequest
{
    /**
     * Checked here rather than in the controller so a non-owner gets a 403
     * before validation can tell them anything about the payload.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('label'));
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
                new HexColor,
                Rule::unique('labels', 'color')
                    ->where('calendar_id', $this->route('label')->calendar_id)
                    ->withoutTrashed()
                    ->ignore($this->route('label')->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('color'))) {
            $this->merge(['color' => strtolower($this->input('color'))]);
        }
    }
}

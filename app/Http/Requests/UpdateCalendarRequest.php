<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    /**
     * Checked here rather than in the controller so a non-owner gets a 403
     * before validation can tell them anything about the payload.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('calendar'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}

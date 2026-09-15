<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'labels' => ['sometimes', 'array'],
            'labels.*.color' => ['required', 'string', 'max:32'],
            'labels.*.name' => ['required', 'string', 'max:255'],
        ];
    }
}

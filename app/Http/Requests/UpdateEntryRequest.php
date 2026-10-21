<?php

namespace App\Http\Requests;

use App\Enums\EntrySymbol;
use App\Rules\HexColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * An entry is its calendar's note for one day, so neither calendar_id nor
 * date can change; moving a note is a delete plus a create.
 */
class UpdateEntryRequest extends FormRequest
{
    /**
     * Checked here rather than in the controller so a non-owner gets a 403
     * before validation can tell them anything about the payload.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('entry'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string'],
            'pen_color' => ['nullable', 'string', 'max:32'],
            'symbol' => ['sometimes', Rule::enum(EntrySymbol::class)],
            'mark_color' => ['nullable', new HexColor],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('mark_color'))) {
            $this->merge(['mark_color' => strtolower($this->input('mark_color'))]);
        }
    }
}

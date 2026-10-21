<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * A colour as "#rrggbb". Day marks are matched against the legend by colour,
 * so every colour must be spelled one way; requests lowercase it beforehand.
 */
class HexColor implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^#[0-9a-f]{6}$/', $value)) {
            $fail('The :attribute must be a colour in the form #rrggbb.');
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPriceRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail("Kaina turi būti teisingo formato (0.10 - 3.00 EUR) su dviem skaičiais po kablelio.");
        }
    }
    public function passes($attribute, $value)
    {
        // Check if the value is a valid decimal between 0.10 and 3
        return preg_match('/^(0\.[1-9]\d*|0\.\d{2}|[1-2](\.\d{1,2})?|3(\.00)?)$/', $value) && $value >= 0.10 && $value <= 3;
    }
}

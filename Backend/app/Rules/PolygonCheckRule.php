<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PolygonCheckRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail("Ploto taškai nenurodyti masyve!");
        } else {
            $firstPoint = $value[0];
            $lastPoint = end($value);
            if ($firstPoint !== $lastPoint) {
                $fail("Pirmas ploto taškas ir paskutinis turi sutapti!");
            } else {
                foreach ($value as $point) {
                    if (!is_array($point) || count($point) !== 2) {
                        $fail("Ploto taškas turi turėti 2 taškus!");
                    } elseif (!is_numeric($point[0]) || !is_numeric($point[1])) {
                        $fail("Ploto taškai turi būti skaičiai!");
                    } elseif ($point[0] < -180 || $point[0] > 180 || $point[1] < -90 || $point[1] > 90) {
                        $fail("Koordinačių ribos: ilgumos turi būti tarp -180 ir 180, platumos tarp -90 ir 90.");
                    }
                }
            }
        }
    }
}

<?php

namespace App\Rules;

use App\Models\Privilege;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PrivilegeCheckRule implements ValidationRule
{
    protected $userId;
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $privilege = Privilege::where('fk_Userid', '=', $this->userId)->where('id', '=', $value)->first();
        if (!$privilege) {
            $fail('Privilegija nerasta sistemoje!');
        } elseif (!$privilege->confirmed) {
            $fail('Privilegija nepatvirtinta sistemoje!');
        } elseif ($privilege->date_until <= Carbon::now()->toDateTimeString()) {
            $fail('Privilegija nebegalioja!');
        }
    }
}

<?php

namespace App\Rules;

use Closure;
use Carbon\Carbon;
use App\Models\Reservation;
use Illuminate\Contracts\Validation\ValidationRule;

class ReservationCheck implements ValidationRule
{
    protected $spaceId;
    public function __construct($spaceId)
    {
        $this->spaceId = $spaceId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newestReservation = Reservation::where('fk_Parking_spaceid', $this->spaceId)
            ->latest('date_until')
            ->first();
        if ($newestReservation) {
            $time = Carbon::createFromFormat('Y-m-d H:i:s', $newestReservation->date_until);
            $now = Carbon::now();
            if ($now < $time) {
                $fail('Rezervuojama vieta jau yra užimta!');
            }
        }
    }
}

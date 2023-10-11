<?php

namespace App\Rules;

use App\Models\Reservation;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReservationChangeTimeRule implements ValidationRule
{
    protected $reservationId;
    public function __construct($reservationId)
    {
        $this->reservationId = $reservationId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // dd($this->reservationId);
        $reservation = Reservation::find($this->reservationId);
        if (!$reservation) {
            $fail("Rezervacija nerasta!");
        } else {
            $dateFrom = Carbon::parse($reservation->date_from);
            if ($dateFrom->addMinutes($value)->lte($reservation->date_until)) {
                $fail("Negalima sumažinti rezervacijos laiko!");
            }
        }
    }
}

<?php

namespace App\Http\Requests\Reservation;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Parking_zone;
use App\Models\Parking_space;
use App\Models\Privilege;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Rules\ReservationChangeTimeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected $rules = [];
    public function rules()
    {
        $this->rules = [
            'time' => ['required', 'integer', 'min:1', 'max:10080', new ReservationChangeTimeRule($this->route('reservation')->id)]
        ];
        return $this->rules;
    }
    public function messages()
    {
        return [
            'time.required' => 'Laiko laukelis privalomas!',
            'time.integer' => 'Netinkamas laiko laukelio formatas!',
            'time.min' => 'Pertrumpas rezervuojamas laikas!',
            'time.max' => 'Perilgas rezervuojamas laikas!'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
    protected function prepareForValidation()
    {
        $parkingZoneId = $this->route('parking_zone')->id;
        $parkingSpaceId = $this->route('parking_space')->id;
        $reservationId = $this->route('reservation')->id;

        $reservation = Reservation::where('id', $reservationId)->first();
        $parking_zone = Parking_zone::where('id', $parkingZoneId)->first();
        $parking_space = Parking_space::where('id', $parkingSpaceId)->first();

        if ($parking_space->fk_Parking_zoneid != $parking_zone->id || $reservation->fk_Parking_spaceid != $parking_space->id)
            throw new NotFoundHttpException(response(['message' => 'Duomenys nerasti'], 404));
        
        $this->merge([
            'id' => $reservation->id
        ]);
    }
    protected function passedValidation()
    {
        $reservation = Reservation::where('id', $this->id)->first();
        $parking_space = Parking_space::where('id', $reservation->fk_Parking_spaceid)->first();
        $parking_zone = Parking_zone::where('id', $parking_space->fk_Parking_zoneid)->first();
        $price = ceil($this->time / $parking_zone->paying_time) * $parking_zone->price;
        if ($reservation->fk_Privilegeid != null) {
            $privilege = Privilege::findOrFail($reservation->fk_Privilegeid);

            $price -= $price * ($privilege->discount / 100);
        }
        $dateFrom = Carbon::parse($reservation->date_from);
        $this->merge([
            'date_until' => $dateFrom->addMinutes(ceil($this->time / $parking_zone->paying_time) * $parking_zone->paying_time)->toDateTimeString(),
            'price' => number_format($price, 2, '.', '')
        ]);
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Parking_space;
use App\Models\Parking_zone;
use App\Models\Reservation;
use App\Rules\PrivilegeCheckRule;
use App\Rules\ReservationChangeTimeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected $rules = [];
    public function rules()
    {
        $method = $this->method();
        if (null !== $this->get('_method', null)) {
            $method = $this->get('_method');
        }
        $this->offsetUnset('_method');
        switch ($method) {
            case 'POST':
                $this->rules = [
                    'fk_Userid' => ['required', 'integer', 'exists:user,id'],
                    'fk_Privilegeid' => ['integer', 'exists:privilege,id', new PrivilegeCheckRule($this->fk_Userid)],
                    'time' => ['required', 'integer', 'min:1', 'max:10080']
                ];
                break;
            case 'PATCH':
                $this->rules = [
                    'time' => ['required', 'integer', 'min:1', 'max:10080', new ReservationChangeTimeRule($this->route('reservation')->id)]
                ];
            default:
                break;
        }

        return $this->rules;
    }
    public function messages()
    {
        return [
            'fk_Userid.required' => 'Vartotojo informacija privaloma!',
            'fk_Userid.integer' => 'Neteisingas vartotojo informacijos formatas!',
            'fk_Userid.exists' => 'Toks vartotojas neegzistuoja!',
            'fk_Privilegeid.integer' => 'Neteisingas vartotojo privilegijos informacijos formatas!',
            'fk_Privilegeid.exists' => 'Tokia privilegija neegzistuoja!',
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
        $method = $this->method();
        if (null !== $this->get('_method', null)) {
            $method = $this->get('_method');
        }
        $this->offsetUnset('_method');
        if ($method == 'PATCH') {
            $parkingZoneId = $this->route('parking_zone')->id;
            $parkingSpaceId = $this->route('parking_space')->id;
            $reservationId = $this->route('reservation')->id;

            $reservation = Reservation::where('id', $reservationId)->first();
            $parking_zone = Parking_zone::where('id', $parkingZoneId)->first();
            $parking_space = Parking_space::where('id', $parkingSpaceId)->first();

            if ($parking_space->fk_Parking_zoneid != $parking_zone->id || $reservation->fk_Parking_spaceid != $parking_space->id)
                throw new NotFoundHttpException(response(['message' => 'Duomenys nerasti'], 404));
        }
    }
}

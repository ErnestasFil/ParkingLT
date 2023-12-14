<?php

namespace App\Http\Requests\Reservation;

use Carbon\Carbon;
use App\Models\Privilege;
use App\Models\Parking_zone;
use App\Models\Parking_space;
use App\Rules\ReservationCheck;
use App\Rules\PrivilegeCheckRule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected $rules = [];
    public function rules()
    {
        $this->rules = [
            'fk_Userid' => ['required', 'integer', 'exists:user,id'],
            'fk_Privilegeid' => ['integer', 'exists:privilege,id', new PrivilegeCheckRule($this->fk_Userid)],
            'time' => ['required', 'integer', 'min:1', 'max:10080', new ReservationCheck($this->fk_Parking_spaceid)]
        ];


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
        $parkingZoneId = $this->route('parking_zone')->id;
        $parkingSpaceId = $this->route('parking_space')->id;

        $parking_zone = Parking_zone::where('id', $parkingZoneId)->first();
        $parking_space = Parking_space::where('id', $parkingSpaceId)->first();

        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            throw new NotFoundHttpException(response(['message' => 'Duomenys nerasti'], 404));

        $sub = JWTAuth::parseToken()->getPayload()->get('sub');
        $this->merge([
            'fk_Userid' => $sub,
            'fk_Parking_spaceid' => $parking_space->id
        ]);
    }
    protected function passedValidation()
    {
        $parking_space = Parking_space::where('id', $this->fk_Parking_spaceid)->first();
        $parking_zone = Parking_zone::where('id', $parking_space->fk_Parking_zoneid)->first();
        $price = ceil($this->time / $parking_zone->paying_time) * $parking_zone->price;
        if ($this->fk_Privilegeid != null) {
            $privilege = Privilege::findOrFail($this->fk_Privilegeid);
            $price -= $price * ($privilege->discount / 100);
        }
        $this->merge([
            'fk_Privilegeid' => $this->fk_Privilegeid != null ? $this->fk_Privilegeid : null,
            'date_from' => Carbon::now()->toDateTimeString(),
            'date_until' => Carbon::now()->addMinutes(ceil($this->time / $parking_zone->paying_time) * $parking_zone->paying_time)->toDateTimeString(),
            'price' => number_format($price, 2, '.', '')
        ]);
    }
}

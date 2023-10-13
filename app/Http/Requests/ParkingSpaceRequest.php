<?php

namespace App\Http\Requests;

use App\Models\Parking_space;
use App\Models\Parking_zone;
use App\Rules\PolygonCheckRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParkingSpaceRequest extends FormRequest
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
                    'space_number' => ['required', 'integer', 'min:1'],
                    'location_polygon' => ['required', new PolygonCheckRule],
                    'invalid_place' => ['required', 'boolean'],
                    'street' => ['required', 'regex:/^[\p{L} \p{N}.]+$/u', 'min:4', 'max:20'],
                    'information' => ['required', 'regex:/^[\p{L} \p{N}.,!-]+$/u', 'min:10', 'max:200']
                ];
                break;
            case 'PUT':
                $this->rules = [
                    'space_number' => ['required', 'integer', 'min:1'],
                    'location_polygon' => ['required', new PolygonCheckRule],
                    'invalid_place' => ['required', 'boolean'],
                    'street' => ['required', 'regex:/^[\p{L} \p{N}.]+$/u', 'min:4', 'max:20'],
                    'information' => ['required', 'regex:/^[\p{L} \p{N}.,!-]+$/u', 'min:10', 'max:200']
                ];
            default:
                break;
        }

        return $this->rules;
    }
    public function messages()
    {
        return [
            'space_number.required' => 'Stovėjimo vietos laukelis privalomas!',
            'space_number.integer' => 'Neteisingas stovėjimo vietos laukelio formatas!',
            'space_number.min' => 'Mažiausias galimas stovėjimo numeris yra 1!',
            'location_polygon.required' => 'Privaloma nurodyti ploto koordinates!',
            'invalid_place.required' => 'Neįgaliojo vietos laukelis privalomas!',
            'invalid_place.boolean' => 'Neteisingas neįgaliojo vietos laukelio formatas!',
            'street.required' => 'Gatvės pavadinimo laukelis privalomas!',
            'street.regex' => 'Neteisingas gatvės pavadinimo laukelio formatas!',
            'street.min' => 'Gatvės pavadinimo laukelio tekstas pertrumpas!',
            'street.max' => 'Gatvės pavadinimo laukelio tekstas perilgas!',
            'information.required' => 'Informacijos laukelis privalomas!',
            'information.regex' => 'Netinkamas informacijos laukelio formatas!',
            'information.min' => 'Informacijos laukelio tekstas pertrumpas!',
            'information.max' => 'Informacijos laukelio tekstas perilgas!',
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
        if ($method == 'PUT') {
            $parkingZoneId = $this->route('parking_zone')->id;
            $parkingSpaceId = $this->route('parking_space')->id;

            $parking_zone = Parking_zone::where('id', $parkingZoneId)->first();
            $parking_space = Parking_space::where('id', $parkingSpaceId)->first();

            if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
                throw new NotFoundHttpException(response(['message' => 'Duomenys nerasti'], 404));
        }
    }
}

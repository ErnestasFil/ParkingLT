<?php

namespace App\Http\Requests;

use App\Rules\PolygonCheckRule;
use App\Rules\ValidPriceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ParkingZoneRequest extends FormRequest
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
                    'name' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:40'],
                    'colour' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
                    'paying_time' => ['required', 'integer', 'min:15', 'max:60'],
                    'price' => ['required', new ValidPriceRule],
                    'location_polygon' => ['required', new PolygonCheckRule],
                    'information' => ['required', 'regex:/^[\p{L} \p{N},!-]+$/u', 'min:10', 'max:200'],
                    'city' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:30']
                ];
                break;
            case 'PUT':
                $this->rules = [
                    'name' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:40'],
                    'colour' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
                    'paying_time' => ['required', 'integer', 'min:15', 'max:60'],
                    'price' => ['required', new ValidPriceRule],
                    'location_polygon' => ['required', new PolygonCheckRule],
                    'information' => ['required', 'regex:/^[\p{L} \p{N}.,!-]+$/u', 'min:10', 'max:200'],
                    'city' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:30']
                ];
            default:
                break;
        }

        return $this->rules;
    }
    public function messages()
    {
        return [
            'name.required' => 'Pavadinimo laukelis privalomas!',
            'name.regex' => 'Neteisingas pavadinimo laukelio formatas!',
            'name.min' => 'Pavadinimo laukelio informacija pertrumpa!',
            'name.max' => 'Pavadinimo laukelio informacija perilga!',
            'colour.required' => 'Spalvos laukelis privalomas!',
            'colour.regex' => 'Neteisingas spalvos laukelio formatas!',
            'paying_time.required' => 'Laiko laukelis privalomas!',
            'paying_time.integer' => 'Neteisingas laiko laukelio formatas!',
            'paying_time.min' => 'Mažiausias galimas laikas yra 15 min!',
            'paying_time.max' => 'Didžiausias galimas laikas yra 60 min!',
            'price.required' => 'Kainos laukelis privalomas!',
            'location_polygon.required' => 'Privaloma nurodyti ploto koordinates!',
            'information.required' => 'Informacijos laukelis privalomas!',
            'information.regex' => 'Netinkamas informacijos laukelio formatas!',
            'information.min' => 'Informacijos laukelio tekstas pertrumpas!',
            'information.max' => 'Informacijos laukelio tekstas perilgas!',
            'city.required' => 'Miesto laukelis privalomas!',
            'city.regex' => 'Miesto laukelio formatas neteisingas!',
            'city.min' => 'Miesto laukelio informacija pertrumpa!',
            'city.max' => 'Miesto laukelio informacija perilga!',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}

<?php

namespace App\Http\Requests\ParkingZone;

use App\Rules\ValidPriceRule;
use App\Rules\PolygonCheckRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'name' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:40'],
            'colour' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'paying_time' => ['required', 'integer', 'min:15', 'max:60'],
            'price' => ['required', new ValidPriceRule],
            'location_polygon' => ['required', new PolygonCheckRule],
            'information' => ['required', 'regex:/^[\p{L} \p{N},!-.]+$/u', 'min:10', 'max:200'],
            'city' => ['required', 'regex:/^[\p{L} ]+$/u', 'min:3', 'max:30']
        ];
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
    protected function passedValidation()
    {
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[1], $point[0]);
            }, $this->location_polygon))
        ], '4326');
        $this->merge([
            'location_polygon' => $polygon,
        ]);
    }
}

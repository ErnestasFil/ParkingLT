<?php

namespace App\Http\Requests\ParkingSpace;

use App\Models\Parking_zone;
use App\Models\Parking_space;
use App\Rules\PolygonCheckRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
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
            'space_number' => ['required', 'integer', 'min:1'],
            'location_polygon' => ['required', new PolygonCheckRule],
            'invalid_place' => ['required', 'boolean'],
            'street' => ['required', 'regex:/^[\p{L} \p{N}.]+$/u', 'min:4', 'max:20'],
            'information' => ['required', 'regex:/^[\p{L} \p{N}.,!-]+$/u', 'min:10', 'max:200']
        ];
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
        $parkingZoneId = $this->route('parking_zone')->id;
        $parkingSpaceId = $this->route('parking_space')->id;

        $parking_zone = Parking_zone::where('id', $parkingZoneId)->first();
        $parking_space = Parking_space::where('id', $parkingSpaceId)->first();

        if ($parking_space->fk_Parking_zoneid != $parking_zone->id)
            throw new NotFoundHttpException(response(['message' => 'Duomenys nerasti'], 404));
    }
    protected function passedValidation()
    {
        $this->merge(["fk_Parking_zoneid" => $this->route('parking_zone')->id]);
        $polygon = new Polygon([
            new LineString(array_map(function ($point) {
                return new Point($point[0], $point[1]);
            }, $this->location_polygon))
        ], '4326');
        $this->merge([
            'fk_Parking_zoneid' => $this->route('parking_zone')->id,
            'invalid_place' => $this->invalid_place ? 1 : 0,
            'location_polygon' => $polygon
        ]);
    }
}

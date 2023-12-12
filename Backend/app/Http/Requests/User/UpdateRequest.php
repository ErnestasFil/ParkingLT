<?php

namespace App\Http\Requests\User;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected $rules = [];

    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'alpha', 'min:3', 'max:25'],
            'surname' => ['sometimes', 'required', 'alpha', 'min:5', 'max:40'],
            'phone' => ['sometimes', 'required', 'phone', 'regex:/^\+[0-9]*$/'],
            'phone_country' => ['required_with:phone', 'regex:/^[a-zA-Z]{2}$/'],
            'password' => [
                'sometimes', 'required', 'string', 'confirmed', Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vardo laukelis privalomas!',
            'name.alpha' => 'Vardo laukelyje galima naudoti tik raides!',
            'name.min' => 'Vardo laukelio informacija pertrumpa!',
            'name.max' => 'Vardo laukelio informacija perilga!',
            'surname.required' => 'Pavardės laukelis privalomas!',
            'surname.alpha' => 'Pavardės laukelyje galima naudoti tik raides!',
            'surname.min' => 'Pavardės laukelio informacija pertrumpa!',
            'surname.max' => 'Pavardės laukelio informacija perilga!',
            'password.required' => 'Slaptažodžio laukelis privalomas!',
            'password.string' => 'Blogas slaptažodžio formatas!',
            'password.min' => 'Slaptažodis pertrumpas!',
            'password.confirmed' => 'Slaptažodžiai nesutampa!',
            'phone.required' => 'Telefono numerio laukelis privalomas!',
            'phone.phone' => 'Blogas telefono numerio formatas!',
            'phone.regex' => 'Telefono numeris turi prasidėti simboliu + ir gali turėti tik skaitmenis.',
            'phone_country' => 'Pasirinkite tinkamą šalies kodą!',
            'phone_country.regex' => 'Netinkamas šalies kodas!'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
    protected function prepareForValidation()
    {
        $sub = JWTAuth::parseToken()->getPayload()->get('sub');
        $user = $this->route('user')->id;
        if ($sub != $user) {
            $this->offsetUnset('password');
            $this->offsetUnset('password_confirmation');
        }
        $this->offsetUnset('email');
        $this->offsetUnset('role');
        $this->offsetUnset('balance');
        $this->offsetUnset('id');
    }
    protected function passedValidation()
    {
        if (isset($this->password)) {
            $this->merge([
                "password" => Hash::make($this->password),
            ]);
        }
    }
}

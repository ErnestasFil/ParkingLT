<?php

namespace App\Http\Requests\Reservation;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Parking_zone;
use App\Models\Parking_space;
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
        return [
            'email' => ['required', 'email'],
            'password' => ['required']
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Elektroninio pašto laukelis privalomas!',
            'email.email' => 'Blogas elektroninio pašto formatas!',
            'password.required' => 'Slaptažodžio laukelis privalomas!',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}

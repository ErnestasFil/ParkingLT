<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    protected $rules = [];
    public function rules()
    {
        return [
            'email' => ['required','email'],
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

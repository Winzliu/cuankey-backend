<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Diatur ke true karena semua pengguna, terlepas dari status autentikasi mereka,
        // harus dapat mengakses endpoint register.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    //  aturan yang sama terhadap userloginrequest
    public function rules(): array
    {
        // berisi aturan validasi yang harus dipenuhi untuk registrasi
        return [
            'fullname'              => ['required', 'string', 'max:255'],
            'phone_number'          => ['required', 'numeric', 'digits_between: 8,15'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'string', 'confirmed', 'min:8'],
            'password_confirmation' => ['required', 'string', 'min:8']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "status"  => "bad request",
            "code"    => 400,
            "message" => "Input data is not valid",
            "errors"  => $validator->getMessageBag()
        ], 400));
    }
}

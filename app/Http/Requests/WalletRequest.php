<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // memastikan bahwa pengguna yang membuat permintaan ini sudah login
        return $this->user() != null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Untuk wallet baru
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|max:255',
                'initial_balance' => 'required|numeric|min:0.01'
            ];
        }

        // validasi untuk wallet yang sudah ada
        if ($this->isMethod('put')) {
            return [
                'name' => 'sometimes|string|max:255',
                'initial_balance' => 'sometimes|numeric|min:0.01'
            ];
        }
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

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RecurringRequest extends FormRequest
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
        // untuk recurring baru
        if ($this->isMethod('post')) {
            return [
                'wallet_id'   => 'required|exists:wallets,id',
                'category_id' => 'required|exists:categories,id',
                'amount'      => 'required|numeric|min:0.01',
                'is_active'   => 'nullable|boolean',
                'description' => 'nullable|string|max:255'
            ];
        }

        // untuk recurring yang sudah ada
        if ($this->isMethod('put')) {
            return [
                'wallet_id'   => 'sometimes|exists:wallets,id',
                'category_id' => 'sometimes|exists:categories,id',
                'amount'      => 'sometimes|required|numeric|min:0.01',
                'is_active'   => 'sometimes|required|boolean',
                'description' => 'nullable|string|max:255'
            ];
        }
    }

    // pesan error validasi yang dilengkapi detail kesalahan
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

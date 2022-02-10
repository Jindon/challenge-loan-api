<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MakePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'integer',
                Rule::in([$this->payment->amount])
            ],
            'currency_code' => [
                'required',
                Rule::in([$this->payment->currency_code])
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'amount.in' => 'Wrong amount provided',
            'currency_code.in' => 'Wrong currency code provided',
        ];
    }
}

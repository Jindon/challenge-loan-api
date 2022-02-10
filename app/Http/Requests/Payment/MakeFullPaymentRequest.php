<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MakeFullPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('loan'));
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
                Rule::in([$this->loan->pending_amount])
            ],
            'currency_code' => [
                'required',
                Rule::in([$this->loan->currency_code])
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

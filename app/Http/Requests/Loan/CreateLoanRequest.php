<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLoanRequest extends FormRequest
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
            'term' => [
                'required',
                'integer'
            ],
            'amount' => [
                'required',
                'integer'
            ],
            'currency_code' => [
                'required',
                Rule::in(config('app.supported_currency_codes'))
            ]
        ];
    }
}

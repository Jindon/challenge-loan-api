<?php

namespace App\Http\Requests\Loan;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class ShowLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if ($this->route('loan')) {
            return $this->user()->can('view', $this->route('loan'));
        }

        return $this->user()->can('view', Loan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'limit' => 'nullable|integer'
        ];
    }
}

<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ShowPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if($this->route('loan')) {
            return $this->user()->can('view', $this->route('loan'));
        }

        return $this->user()->can('view', Payment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'limit' => [
                'nullable',
                'integer'
            ]
        ];
    }
}

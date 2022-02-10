<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'paid' => (bool) $this->paid,
            'currency_code' => $this->currency_code,
            'amount' => $this->amount / 100,
            'due_on' => $this->due_on?->format('Y-m-d'),
            'paid_on' => $this->paid_on?->format('Y-m-d H:i:s a'),
            'user_id' => $this->user_id,
            'loan_id' => $this->loan_id,
            'loan' => new LoanResource($this->whenLoaded('loan')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_no' => $this->account_no,
            'status' => $this->status,
            'currency_code' => $this->currency_code,
            'amount' => $this->amount / 100,
            'term' => $this->term,
            'paid_amount' => $this->paid_amount / 100,
            'pending_amount' => $this->pending_amount / 100,
            'issued_on' => $this->issued_on?->format('Y-m-d'),
            'user_id' => $this->user_id,
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}

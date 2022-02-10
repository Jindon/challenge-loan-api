<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\MakePaymentRequest;
use App\Http\Requests\Payment\ShowPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Loan;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    /**
     * @param ShowPaymentRequest $request
     * @param Loan $loan
     * @return AnonymousResourceCollection
     */
    public function index(ShowPaymentRequest $request, Loan $loan): AnonymousResourceCollection
    {
        $payments = QueryBuilder::for(Payment::class)
            ->where('loan_id', $loan->id)
            ->defaultSort('due_on')
            ->allowedIncludes(['loan'])
            ->allowedFilters([
                AllowedFilter::scope('paid'),
                AllowedFilter::scope('pending'),
            ])
            ->paginate($request->get('limit', 10));

        return PaymentResource::collection($payments);
    }

    /**
     * @param MakePaymentRequest $request
     * @param Loan $loan
     * @param Payment $payment
     * @param PaymentService $paymentService
     * @return PaymentResource|JsonResponse
     */
    public function update(
        MakePaymentRequest $request,
        Loan $loan,
        Payment $payment,
        PaymentService $paymentService
    ): PaymentResource|JsonResponse
    {
        try {
            $paidPayment = $paymentService->makePayment($payment);
            return new PaymentResource($paidPayment);
        } catch(\Exception $error) {
            return $this->errorResponse($error->getMessage());
        }
    }
}

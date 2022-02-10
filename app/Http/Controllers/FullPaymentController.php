<?php

namespace App\Http\Controllers;


use App\Http\Requests\Payment\MakeFullPaymentRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;

class FullPaymentController extends Controller
{
    /**
     * @param LoanService $loanService
     */
    public function __construct(
        protected LoanService $loanService
    ){}

    /**
     * @param MakeFullPaymentRequest $request
     * @param Loan $loan
     * @return LoanResource|JsonResponse
     */
    public function __invoke(MakeFullPaymentRequest $request, Loan $loan): JsonResponse|LoanResource
    {
        try {
            $paidLoan = $this->loanService->payAll($loan);
            return new LoanResource($paidLoan);
        } catch(\Exception $error) {
            return $this->errorResponse($error->getMessage());
        }
    }
}

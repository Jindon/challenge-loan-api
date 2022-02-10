<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\CreateLoanRequest;
use App\Http\Requests\Loan\ShowLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LoanController extends Controller
{
    /**
     * @param ShowLoanRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(ShowLoanRequest $request): AnonymousResourceCollection
    {
        $loans = QueryBuilder::for(Loan::class)
            ->where('user_id', auth()->id())
            ->allowedFilters([
                AllowedFilter::exact('status'),
            ])
            ->paginate($request->get('limit', 10));

        return LoanResource::collection($loans);
    }

    /**
     * @param ShowLoanRequest $request
     * @param Loan $loan
     * @return LoanResource
     */
    public function show(ShowLoanRequest $request, Loan $loan): LoanResource
    {
        $loanData = QueryBuilder::for($loan)
            ->allowedIncludes(['payments'])
            ->find($loan->id);

        return new LoanResource($loanData);
    }

    /**
     * @param CreateLoanRequest $request
     * @param LoanService $loanService
     * @return LoanResource|JsonResponse
     */
    public function store(CreateLoanRequest $request, LoanService $loanService): JsonResponse|LoanResource
    {
        try {
            $loan = $loanService->create(
                user: User::find(auth()->id()),
                amount: $request->amount,
                term: $request->term,
                currencyCode: $request->currency_code
            );

            return new LoanResource($loan);
        } catch(\Exception $error) {
            return $this->errorResponse($error->getMessage());
        }
    }
}

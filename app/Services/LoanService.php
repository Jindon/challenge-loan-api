<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * @param User $user
     * @param int $amount
     * @param int $term
     * @param string $currencyCode
     * @return Loan
     */
    public function create(User $user, int $amount, int $term, string $currencyCode): Loan
    {
        return Loan::create([
            'amount' => $amount,
            'term' => $term,
            'currency_code' => $currencyCode,
            'user_id' => $user->id
        ]);
    }

    /**
     * @param Loan $loan
     * @param Carbon $issuedOn
     * @return Loan
     * @throws \Exception
     */
    public function approve(Loan $loan, string $issuedOn): Loan
    {
        try {
            if($loan->status === LoanStatus::ONGOING) {
                throw new \Exception('Loan already approved');
            }

            DB::beginTransaction();
            $loan->update([
                'issued_on' => $issuedOn,
                'pending_amount' => $loan->amount,
                'status' => LoanStatus::ONGOING,
                'account_no' => sprintf("ASP%08d", $loan->id)
            ]);

            (new PaymentService())->generatePayments($loan);
            DB::commit();

            return $loan->fresh();
        } catch (\Exception $error) {
            DB::rollBack();
            throw $error;
        }
    }

    /**
     * @param Loan $loan
     * @return Loan
     */
    public function close(Loan $loan, bool $updatePendingAndPaidAmount = false): Loan
    {
        $attributes = [
            'status' => LoanStatus::CLOSED,
        ];

        if($updatePendingAndPaidAmount) {
            $attributes['pending_amount'] = 0;
            $attributes['paid_amount'] = $loan->amount;
        }
        $loan->update($attributes);
        return $loan->fresh();
    }

    /**
     * @param Loan $loan
     * @return Loan
     */
    public function reject(Loan $loan): Loan
    {
        $loan->update([
            'status' => LoanStatus::REJECTED,
        ]);
        return $loan->fresh();
    }

    /**
     * @param Loan $loan
     * @return Loan
     */
    public function payAll(Loan $loan): Loan
    {
        try {
            if ($loan->status === LoanStatus::CLOSED) {
                throw new \Exception('Loan already closed');
            }

            DB::beginTransaction();
            $loan->payments()->pending()->update([
                'paid_on' => now()
            ]);

            $this->close($loan, true);
            DB::commit();

            return $loan->fresh();
        } catch(\Exception $error) {
            DB::rollBack();
            throw $error;
        }
    }
}

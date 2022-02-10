<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * @param Loan $loan
     */
    public function generatePayments(Loan $loan): void
    {
        $fractionAmount = $loan->amount % $loan->term;
        $termAmount = ($loan->amount - $fractionAmount) / $loan->term;

        for($i = 0; $i < $loan->term; $i++) {
            $amount = $termAmount;
            // To prevent fractions, the fractional value from the mod operation is
            // spread across the payments for each term until the complete value is added
           if($i < $fractionAmount) {
               $amount += 1;
           }

           // generate due date for weekly payments by adding on weeks based on the payment term
           $dueOn = $loan->issued_on->addWeeks($i + 1);

           Payment::updateOrCreate([
               'amount' => $amount,
               'due_on' => $dueOn,
               'loan_id' => $loan->id,
           ], [
               'currency_code' => $loan->currency_code,
               'amount' => $amount,
               'due_on' => $dueOn,
               'loan_id' => $loan->id,
               'user_id' => $loan->user_id,
           ]);
        }
    }

    /**
     * @param Payment $payment
     * @param string|null $paidOn
     * @return Payment
     * @throws \Exception
     */
    public function makePayment(Payment $payment): Payment
    {
        try {
            if ($payment->paid) {
                throw new \Exception('Already paid');
            }

            DB::beginTransaction();
            // Mark as paid
            $payment->update([
                'paid_on' => now(),
            ]);

            // Update load pending and paid amounts
            $payment->loan()->update([
                'paid_amount' => $payment->loan->paid_amount + $payment->amount,
                'pending_amount' => $payment->loan->pending_amount - $payment->amount,
            ]);

            // If there is no more remaining payments, close the loan
            $remainingPaymentCount = Payment::where('loan_id', $payment->loan_id)->whereNull('paid_on')->count();
            if(!$remainingPaymentCount) {
                (new LoanService())->close($payment->loan);
            }
            DB::commit();

            return $payment->fresh();
        } catch (\Exception $error) {
            DB::rollBack();
            throw $error;
        }
    }
}

<?php

namespace Tests\Unit;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->paymentService = new PaymentService();
    }

    public function test_service_can_generate_payments_for_approved_loan()
    {
        $issuedOn = '2022-02-10';
        $loan = Loan::factory()->approved()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $this->user->id,
            'issued_on' => $issuedOn
        ]);

        $this->assertDatabaseCount('payments', 0);

        $this->paymentService->generatePayments($loan);

        //Assert payments are generated
        $this->assertDatabaseCount('payments', 3);

        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 101, // $amount % $term = 3, so 2 entries will be 100 + 1
            'paid_on' => null
        ]);
        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 101, // $amount % $term = 3, so 2 entries will be 100 + 1
            'paid_on' => null
        ]);
        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 100,
            'paid_on' => null
        ]);
    }

    public function test_service_generated_payments_have_weekly_due_dates()
    {
        $issuedOn = '2022-02-10';
        $term = 3;
        $loan = Loan::factory()->approved()->create([
            'amount' => 302,
            'term' => $term,
            'user_id' => $this->user->id,
            'issued_on' => $issuedOn
        ]);

        $this->paymentService->generatePayments($loan);

        //Assert correct payments count are generated
        $this->assertDatabaseCount('payments', $term);

        // Assert payments with correct due date and amount are generated
        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 101, // $amount % $term = 2, so 2 entries will be 100 + 1
            'paid_on' => null,
            'due_on' => $loan->issued_on->addWeeks(1),
        ]);
        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 101, // $amount % $term = 2, so 2 entries will be 100 + 1
            'paid_on' => null,
            'due_on' => $loan->issued_on->addWeeks(2),
        ]);
        $this->assertDatabaseHas('payments', [
            'loan_id' => $loan->id,
            'user_id' => $this->user->id,
            'currency_code' => $loan->currency_code,
            'amount' => 100, // $amount % $term = 2, so since this is the 3rd payment, amount will be ($amount - 2)/3 = 100
            'paid_on' => null,
            'due_on' => $loan->issued_on->addWeeks(3),
        ]);

        // Assert generate payments total amount equals loan amount
        $paymentsTotal = $loan->payments()->sum('amount');
        $this->assertEquals($paymentsTotal, $loan->amount);
    }

    public function test_service_can_make_a_payment()
    {
        $loan = Loan::factory()->approved()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $this->user->id
        ]);

        $this->paymentService->generatePayments($loan);

        $payment = $loan->payments()->first();

        $this->paymentService->makePayment($payment);

        // Assert payment is made
        $this->assertNotNull($payment->paid_on);
    }

    public function test_service_updates_loan_pending_and_paid_amounts_when_making_a_payment()
    {
        $loan = Loan::factory()->approved()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $this->user->id
        ]);

        $this->paymentService->generatePayments($loan);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => $loan->pending_amount,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::ONGOING,
        ]);

        $payment = $loan->payments()->first();

        $paidPayment = $this->paymentService->makePayment($payment);

        // Assert payment is updated in loan
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0 + $paidPayment->amount,
            'pending_amount' => $loan->pending_amount - $paidPayment->amount,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::ONGOING,
        ]);
    }

    public function test_service_closes_loan_when_making_last_payment()
    {
        $loan = Loan::factory()
            ->approved([
                'pending_amount' => 302
            ])->create([
                'amount' => 302,
                'term' => 3,
                'user_id' => $this->user->id
            ]);

        $this->paymentService->generatePayments($loan);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => $loan->pending_amount,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::ONGOING,
        ]);

        [$payment1, $payment2, $payment3] = $loan->payments;

        $this->paymentService->makePayment($payment1);
        $this->paymentService->makePayment($payment2);

        // Assert paid and pending amounts are correct
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => $payment1->amount + $payment2->amount,
            'pending_amount' => $payment3->amount,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::ONGOING,
        ]);

        // Make last payment
        $this->paymentService->makePayment($payment3);

        // Assert loan is closed
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => $loan->amount,
            'pending_amount' => 0,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::CLOSED,
        ]);
    }

    public function test_service_cannot_make_a_payment_if_already_paid()
    {
        $loan = Loan::factory()->approved()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $this->user->id
        ]);

        $this->paymentService->generatePayments($loan);

        $payment = $loan->payments()->first();

        // Make payment
        $this->paymentService->makePayment($payment);

        // Expect exception
        $this->expectException("Exception");
        $this->expectExceptionMessage("Already paid");

        // Attempt payment for same paid payment
        $this->paymentService->makePayment($payment);
    }
}

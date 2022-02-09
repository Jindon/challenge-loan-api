<?php

namespace Tests\Unit;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected LoanService $loanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->loanService = new LoanService();
    }

    public function test_service_can_create_loan_application()
    {
        $term = 3;
        $amount = 5000;
        $currencyCode = 'USD';

        $loan = $this->loanService->create($this->user, $amount, $term, $currencyCode);

        // Asserting Loan details
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $amount,
            'term' => $term,
            'currency_code' => $currencyCode,
            'paid_amount' => 0,
            'pending_amount' => 0,
            'issued_on' => null,
            'status' => LoanStatus::PENDING,
        ]);
    }

    public function test_service_can_approve_loan_application()
    {
        $loan = Loan::factory()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $this->user->id
        ]);
        $issuedOn = '2022-02-10';

        // Asserting pending Loan details
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => 0,
            'issued_on' => null,
            'status' => LoanStatus::PENDING,
        ]);

        $this->loanService->approve($loan, $issuedOn);
        $loan->fresh();

        // Asserting approved Loan details
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => $loan->pending_amount,
            'issued_on' => $issuedOn,
            'status' => LoanStatus::ONGOING,
        ]);

        // Assert account number generation
        $this->assertEquals($loan->account_no, sprintf("ASP%08d", $loan->id));

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

    public function test_service_cannot_approve_already_approved_loan_application()
    {
        $approvedLoan = Loan::factory()->approved()->create([
            'user_id' => $this->user->id
        ]);

        // Asserting Loan details
        $this->assertDatabaseHas('loans', [
            'id' => $approvedLoan->id,
            'user_id' => $this->user->id,
            'amount' => $approvedLoan->amount,
            'term' => $approvedLoan->term,
            'currency_code' => $approvedLoan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => $approvedLoan->pending_amount,
            'issued_on' => $approvedLoan->issued_on,
            'status' => LoanStatus::ONGOING,
        ]);

        $this->expectException("Exception");
        $this->expectExceptionMessage("Loan already approved");

        $this->loanService->approve($approvedLoan, '2022-02-10');

    }

    public function test_service_can_close_a_loan()
    {
        $loan = Loan::factory()->approved()->create([
            'user_id' => $this->user->id
        ]);

        // Asserting Loan details
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

        $this->loanService->close($loan);

        // Asserting Loan is closed
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::CLOSED,
        ]);
    }

    public function test_service_can_reject_a_loan()
    {
        $loan = Loan::factory()->approved()->create([
            'user_id' => $this->user->id
        ]);

        // Asserting Loan details
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

        $this->loanService->reject($loan);

        // Asserting Loan is closed
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'issued_on' => $loan->issued_on,
            'status' => LoanStatus::REJECTED,
        ]);
    }

    public function test_service_can_fully_pay_a_loan()
    {
        $loan = Loan::factory()->create([
            'term' => 3,
            'user_id' => $this->user->id
        ]);
        $issuedOn = '2022-02-10';
        $this->loanService->approve($loan, $issuedOn);
        $loan->fresh();

        // Asserting approved Loan details
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => 0,
            'pending_amount' => $loan->pending_amount,
            'issued_on' => $issuedOn,
            'status' => LoanStatus::ONGOING,
        ]);

        // Assert pending payments count generated
        $this->assertEquals($loan->payments()->pending()->count(), 3);

        $this->loanService->payAll($loan);

        $this->assertEquals($loan->payments()->pending()->count(), 0);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $loan->amount,
            'term' => $loan->term,
            'currency_code' => $loan->currency_code,
            'paid_amount' => $loan->amount,
            'pending_amount' => 0,
            'issued_on' => $issuedOn,
            'status' => LoanStatus::CLOSED,
        ]);
    }
}

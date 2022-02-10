<?php

namespace Tests\Unit\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use App\Services\LoanService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveLoanTest extends TestCase
{
    use RefreshDatabase;

    protected Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->loan = Loan::factory()->create([
            'amount' => 302,
            'term' => 3,
            'user_id' => $user->id
        ]);
    }

    public function test_command_can_approve_a_loan()
    {
        $this->assertDatabaseHas('loans', [
            'id' => $this->loan->id,
            'status' => LoanStatus::PENDING,
            'pending_amount' => 0
        ]);

        $this->artisan("loan:approve {$this->loan->id}")->assertSuccessful();

        $this->assertDatabaseHas('loans', [
            'id' => $this->loan->id,
            'status' => LoanStatus::ONGOING,
            'pending_amount' => $this->loan->amount
        ]);
    }

    public function test_command_cannot_approve_already_approved_loan()
    {
        $this->artisan("loan:approve {$this->loan->id}")->assertSuccessful();

        // Rerun command on approved loan to assert a failure
        $this->artisan("loan:approve {$this->loan->id}")->assertFailed();

    }

    public function test_command_cannot_approve_non_existent_loan()
    {
        $this->artisan("loan:approve 123")->assertFailed();

    }
}

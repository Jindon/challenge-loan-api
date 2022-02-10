<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FullPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_pay_all_payments_of_a_loan_at_once()
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'term' => 5,
            'amount' => 2_000_000
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/loans/{$loan->id}/payments/pay-full", [
                'amount' => $loan->pending_amount,
                'currency_code' => $loan->currency_code
            ]);

        // Assert OK response with data
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->where('data.id', $loan->id)
                ->where('data.pending_amount', 0)
                ->where('data.paid_amount', $loan->amount / 100)
                ->where('data.status', LoanStatus::CLOSED->value)
                ->etc()
            );
    }

    public function test_user_cannot_pay_all_payments_of_a_different_user_loan()
    {
        $differentUser = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $differentUser->id,
            'term' => 5,
            'amount' => 2_000_000
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/loans/{$loan->id}/payments/pay-full", [
                'amount' => $loan->pending_amount,
                'currency_code' => $loan->currency_code
            ]);

        // Assert unauthorized response
        $response->assertStatus(403);
    }
}

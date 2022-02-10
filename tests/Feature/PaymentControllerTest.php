<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_users_can_view_their_loan_payments()
    {
        // create a loan for the user
        $term = 5; // less than 10 to avoid pagination
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'term' => $term,
            'amount' => 2000000
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));
        $loan->fresh();

        // Assert if payments are generated
        $this->assertDatabaseCount('payments', $term);

        $response = $this->actingAs($this->user)
            ->getJson("/api/loans/{$loan->id}/payments");

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                    ->has('links')
                    ->has('data', $term, fn ($json) =>
                        $json->where('user_id', $this->user->id)
                            ->where('loan_id', $loan->id)
                            ->etc()
                    )
            );
    }

    public function test_users_cannot_view_loan_payments_of_other_users()
    {
        // create a loan for the user
        $differentUser = User::factory()->create();
        $term = 5;
        $loan = Loan::factory()->create([
            'user_id' => $differentUser->id,
            'term' => $term,
            'amount' => 2000000
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));
        $loan->fresh();

        // Assert if payments are generated
        $this->assertDatabaseCount('payments', $term);

        $response = $this->actingAs($this->user)
            ->getJson("/api/loans/{$loan->id}/payments");

        // Assert unauthorized response
        $response->assertStatus(403);
    }

    public function test_users_can_make_a_payment()
    {
        $term = 5;
        $amount = 2_000_000;
        $paymentAmount = 400_000; // [$amount / $term]
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'term' => $term,
            'amount' => $amount
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));

        $payment = Payment::where('loan_id', $loan->id)
            ->latest('due_on')
            ->first();

        // Assert if payments are generated
        $this->assertDatabaseCount('payments', $term);

        $response = $this->actingAs($this->user)
            ->putJson("/api/loans/{$loan->id}/payments/{$payment->id}", [
                'amount' => $paymentAmount,
                'currency_code' => $loan->currency_code
            ]);

        // Assert OK response with data
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data')
                    ->where('data.id', $payment->id)
                    ->where('data.amount', $paymentAmount / 100) // converting smallest denomination to actual amt
                    ->where('data.paid', true)
                    ->etc()
            );
    }

    public function test_users_cannot_make_payment_for_different_user()
    {
        $differentUser = User::factory()->create();
        $term = 5;
        $amount = 2_000_000;
        $paymentAmount = 400_000; // [$amount / $term]
        $loan = Loan::factory()->create([
            'user_id' => $differentUser->id,
            'term' => $term,
            'amount' => $amount
        ]);

        // Approve loan to generate payments
        (new LoanService())->approve($loan, now()->format('Y-m-d'));

        $payment = Payment::where('loan_id', $loan->id)
            ->latest('due_on')
            ->first();

        // Assert if payments are generated
        $this->assertDatabaseCount('payments', $term);

        $response = $this->actingAs($this->user)
            ->putJson("/api/loans/{$loan->id}/payments/{$payment->id}", [
                'amount' => $paymentAmount,
                'currency_code' => $loan->currency_code
            ]);

        // Assert unauthorized response
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_users_can_view_their_loans()
    {
        // Loans for user
        Loan::factory(10)->create([
            'user_id' => $this->user->id
        ]);

        // Loans for different users
        Loan::factory(10)->create();

        // Assert if all the loans for all users are in DB
        $this->assertDatabaseCount('loans', 20);

        $response = $this->actingAs($this->user)
            ->getJson('/api/loans');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('meta')
                ->has('links')
                // Assert only user loans are returned
                ->has('data', 10, fn ($json) =>
                $json->where('user_id', $this->user->id)
                    ->etc()
                )
            );
    }

    public function test_users_can_view_a_single_loan_that_belongs_to_the_user()
    {
        // Loans for user
        Loan::factory(10)->create([
            'user_id' => $this->user->id
        ]);

        $loan = Loan::first();

        $response = $this->actingAs($this->user)
            ->getJson("/api/loans/{$loan->id}");

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->where('data.id', $loan->id)
                ->where('data.user_id', $this->user->id)
                ->etc()
            );
    }

    public function test_users_cannot_view_loan_that_belongs_to_different_user()
    {
        $differentUser = User::factory()->create();
        // Loan for different user
        $loan = Loan::factory()->create([
            'user_id' => $differentUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/loans/{$loan->id}");

        // Assert unauthorized status
        $response->assertStatus(403);
    }

    public function test_users_can_submit_a_loan_application()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/loans", [
                'amount' => 25000,
                'term' => 10,
                'currency_code' => 'USD'
            ]);

        // Assert created status
        $response->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('data')
                ->where('data.amount', (25000 / 100))
                ->where('data.term', 10)
                ->where('data.currency_code', 'USD')
                ->etc()
            );
    }

    public function test_term_is_required_in_loan_application()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/loans", [
                'amount' => 25000,
                'currency_code' => 'USD'
            ]);

        // Assert created status
        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors')
            );
    }

    public function test_amount_is_required_in_loan_application()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/loans", [
                'term' => 10,
                'currency_code' => 'USD'
            ]);

        // Assert created status
        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors')
            );
    }

    public function test_currency_code_is_required_in_loan_application()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/loans", [
                'amount' => 25000,
                'term' => 10
            ]);

        // Assert created status
        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors')
            );
    }
}

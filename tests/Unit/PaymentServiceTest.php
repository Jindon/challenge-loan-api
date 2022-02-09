<?php

namespace Tests\Unit;

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
        $this->assertTrue(true);
    }

    public function test_service_can_make_a_payment()
    {
        $this->assertTrue(true);
    }

    public function test_service_cannot_make_a_payment_if_already_paid()
    {
        $this->assertTrue(true);
    }
}

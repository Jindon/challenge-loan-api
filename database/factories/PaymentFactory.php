<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $loan = Loan::factory()->approved()->create();
        return [
            'currency_code' => $loan->currency_code,
            'amount' => $this->faker->numberBetween(2, 10) * 10_000,
            'user_id' => User::factory(),
            'loan_id' => $loan->id,
        ];
    }
}

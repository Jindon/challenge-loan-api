<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'currency_code' => collect(config('app.supported_currency_codes'))->random(),
            'amount' => $this->faker->numberBetween(100000, 1000000),
            'term' => $this->faker->numberBetween(10, 104),
            'user_id' => User::factory()
        ];
    }

    /**
     * Make loan approved
     *
     * @param array $states
     * @return LoanFactory
     */
    public function approved(array $states = []): LoanFactory
    {
        return $this->state(function (array $attributes) use($states) {
            return array_merge([
                'status' => LoanStatus::ONGOING,
                'paid_amount' => 0,
                'pending_amount' => $attributes['amount'],
                'issued_on' => $this->faker->dateTimeBetween('-5 months')
            ], $states);
        });
    }
}

<?php

namespace Database\Factories;

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
            'amount' => $this->faker->numberBetween(20, 1000) * 10_000,
            'term' => $this->faker->numberBetween(10, 104),
            'user_id' => User::factory()
        ];
    }
}

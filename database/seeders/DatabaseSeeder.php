<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'email' => 'test@test.com'
        ]);
        User::factory(5)->create();
//        Loan::factory(10)->create();
    }
}

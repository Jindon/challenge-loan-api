<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ?Payment $payment = null): bool
    {
        if(! $payment) {
            return true;
        }

        return $user->is($payment->user);
    }

    public function update(User $user, Payment $payment)
    {
        return $user->is($payment->user);
    }
}

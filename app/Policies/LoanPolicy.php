<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Loan|null $loan
     * @return bool
     */
    public function view(User $user, ?Loan $loan = null): bool
    {
        if (!$loan) {
            return true;
        }

        return $user->is($loan->user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->is($loan->user);
    }
}

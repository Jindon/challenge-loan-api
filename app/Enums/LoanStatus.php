<?php

namespace App\Enums;

enum LoanStatus : string
{
    case PENDING = 'pending';
    case ONGOING = 'ongoing';
    case CLOSED = 'closed';
    case REJECTED = 'rejected';
}

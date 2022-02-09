<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
      'account_no',
      'status',
      'currency_code',
      'amount',
      'term',
      'paid_amount',
      'pending_amount',
      'issued_on',
      'user_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'status' => LoanStatus::class
    ];

    /**
     * The attributes that should be cast to date.
     *
     * @var string[]
     */
    protected $dates = [
        'issued_on'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

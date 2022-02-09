<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $appends = [
        'paid'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'currency_code',
        'amount',
        'due_on',
        'paid_on',
        'user_id',
        'loan_id',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'due_on',
        'paid_on'
    ];

    /**
     * Interact with the payment status.
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function paid(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => ! is_null($attributes['paid_on']),
        );
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}

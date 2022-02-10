<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
    protected $casts = [
        'due_on' => 'date:Y-m-d',
        'paid_on' => 'date:Y-m-d',
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
            get: fn ($value, $attributes) => ! is_null(data_get($attributes, 'paid_on')),
        );
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('paid_on');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->whereNotNull('paid_on');
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

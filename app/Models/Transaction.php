<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'event_day_id',
        'shift_id',
        'cashier_id',
        'total_items',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'status',
        'idempotency_key',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_items' => 'integer',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function isCorrected(): bool
    {
        return $this->status === 'corrected';
    }

    public function isValid(): bool
    {
        return $this->status !== 'voided';
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', '!=', 'voided');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function eventDay(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(TransactionCorrection::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp'.number_format((float) $this->total_amount, 0, ',', '.');
    }
}

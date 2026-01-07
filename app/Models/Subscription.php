<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->stripe_status === 'active' || $this->isOnTrial();
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isCancelled(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isExpired(): bool
    {
        return !$this->isActive() && !$this->isOnTrial();
    }

    public function isOnGracePeriod(): bool
    {
        if (!$this->ends_at) {
            return false;
        }

        return $this->ends_at->isFuture() && $this->stripe_status === 'canceled';
    }
}
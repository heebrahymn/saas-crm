<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Company extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'subdomain',
        'email',
        'address',
        'phone',
        'settings',
        'trial_ends_at',
        'subscribed_until',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'trial_will_end_at',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'tax_exempt',
        'extra_billing_information',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_until' => 'datetime',
        'trial_will_end_at' => 'datetime',
        'card_brand' => 'datetime',
        'card_last_four' => 'datetime',
        'card_country' => 'datetime',
        'billing_address' => 'datetime',
        'billing_city' => 'datetime',
        'billing_state' => 'datetime',
        'billing_zip' => 'datetime',
        'billing_country' => 'datetime',
        'tax_exempt' => 'datetime',
        'extra_billing_information' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed_until?->isFuture() ?? false;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at?->isFuture() ?? false;
    }

    public function hasExpired(): bool
    {
        return !$this->isSubscribed() && !$this->isOnTrial();
    }
}
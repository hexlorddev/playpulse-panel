<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'billing_plan_id',
        'status',
        'payment_gateway',
        'gateway_subscription_id',
        'amount',
        'currency',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
        'expires_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the billing plan for this subscription.
     */
    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at;
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get days until expiration.
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }

        return max(0, $this->current_period_end->diffInDays(now()));
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return null;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    /**
     * Mark subscription as cancelled.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(): void
    {
        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);
    }

    /**
     * Scope active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope trial subscriptions.
     */
    public function scopeOnTrial($query)
    {
        return $query->where('trial_ends_at', '>', now());
    }

    /**
     * Scope by gateway.
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }
}
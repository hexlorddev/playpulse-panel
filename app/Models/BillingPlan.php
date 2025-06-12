<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_period',
        'memory_limit',
        'cpu_limit',
        'disk_limit',
        'server_limit',
        'database_limit',
        'backup_limit',
        'user_limit',
        'features',
        'priority_support',
        'active',
        'featured',
        'sort_order',
        'stripe_price_id',
        'paypal_plan_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'priority_support' => 'boolean',
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscriptions for this plan.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->where('status', 'active');
    }

    /**
     * Scope active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope featured plans.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope by billing period.
     */
    public function scopeByPeriod($query, string $period)
    {
        return $query->where('billing_period', $period);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get yearly price if monthly.
     */
    public function getYearlyPriceAttribute(): float
    {
        return $this->billing_period === 'monthly' ? $this->price * 12 : $this->price;
    }

    /**
     * Get monthly price if yearly.
     */
    public function getMonthlyPriceAttribute(): float
    {
        return $this->billing_period === 'yearly' ? $this->price / 12 : $this->price;
    }

    /**
     * Check if plan has a feature.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get all available features.
     */
    public static function getAvailableFeatures(): array
    {
        return [
            'priority_support' => 'Priority Support',
            'advanced_monitoring' => 'Advanced Monitoring',
            'custom_domains' => 'Custom Domains',
            'ssl_certificates' => 'SSL Certificates',
            'ddos_protection' => 'DDoS Protection',
            'automated_backups' => 'Automated Backups',
            'server_migration' => 'Server Migration',
            'api_access' => 'API Access',
            'white_label' => 'White Label',
            'reseller_access' => 'Reseller Access',
        ];
    }
}
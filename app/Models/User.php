<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'language',
        'timezone',
        'two_factor_enabled',
        'two_factor_secret',
        'recovery_codes',
        'last_activity',
        'email_verified_at',
        'suspended',
        'suspension_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'recovery_codes' => 'array',
        'last_activity' => 'datetime',
        'suspended' => 'boolean',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty();
    }

    /**
     * Get the user's servers.
     */
    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    /**
     * Get the user's current subscription.
     */
    public function subscription()
    {
        return $this->hasOne(UserSubscription::class)->where('status', 'active');
    }

    /**
     * Get all of the user's subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription()->exists();
    }

    /**
     * Get the user's current plan.
     */
    public function currentPlan()
    {
        return $this->subscription?->billingPlan;
    }

    /**
     * Check if user can create more servers.
     */
    public function canCreateServer(): bool
    {
        $plan = $this->currentPlan();
        if (!$plan) {
            return false;
        }

        return $this->servers()->count() < $plan->server_limit;
    }

    /**
     * Get remaining server slots.
     */
    public function remainingServerSlots(): int
    {
        $plan = $this->currentPlan();
        if (!$plan) {
            return 0;
        }

        return max(0, $plan->server_limit - $this->servers()->count());
    }
}
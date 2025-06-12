<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;

class Server extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'uuid',
        'user_id',
        'server_template_id',
        'status',
        'game',
        'port',
        'ip_address',
        'environment_variables',
        'startup_command',
        'memory_limit',
        'cpu_limit',
        'disk_limit',
        'database_limit',
        'backup_limit',
        'suspended',
        'suspension_reason',
        'last_activity',
        'resource_usage',
        'container_id',
        'node_id',
        'player_count',
        'max_players',
        'configuration',
        'auto_start',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'environment_variables' => 'array',
        'resource_usage' => 'array',
        'configuration' => 'array',
        'suspended' => 'boolean',
        'auto_start' => 'boolean',
        'last_activity' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($server) {
            if (empty($server->uuid)) {
                $server->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'suspended'])
            ->logOnlyDirty();
    }

    /**
     * Get the server's owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the server's template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ServerTemplate::class, 'server_template_id');
    }

    /**
     * Get the server's node.
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    /**
     * Get the server's files.
     */
    public function files(): HasMany
    {
        return $this->hasMany(ServerFile::class);
    }

    /**
     * Get the server's backups.
     */
    public function backups(): HasMany
    {
        return $this->hasMany(ServerBackup::class);
    }

    /**
     * Get the server's logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ServerLog::class);
    }

    /**
     * Check if server is online.
     */
    public function isOnline(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if server is offline.
     */
    public function isOffline(): bool
    {
        return in_array($this->status, ['stopped', 'crashed']);
    }

    /**
     * Check if server is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    /**
     * Get the server's current resource usage percentage.
     */
    public function getResourceUsageAttribute($value)
    {
        $usage = json_decode($value, true);
        if (!$usage) {
            return [
                'cpu' => 0,
                'memory' => 0,
                'disk' => 0,
                'network_in' => 0,
                'network_out' => 0,
            ];
        }

        return $usage;
    }

    /**
     * Get memory usage percentage.
     */
    public function getMemoryUsagePercentage(): float
    {
        $usage = $this->resource_usage;
        return $this->memory_limit > 0 ? ($usage['memory'] / $this->memory_limit) * 100 : 0;
    }

    /**
     * Get CPU usage percentage.
     */
    public function getCpuUsagePercentage(): float
    {
        $usage = $this->resource_usage;
        return $usage['cpu'] ?? 0;
    }

    /**
     * Get disk usage percentage.
     */
    public function getDiskUsagePercentage(): float
    {
        $usage = $this->resource_usage;
        return $this->disk_limit > 0 ? ($usage['disk'] / $this->disk_limit) * 100 : 0;
    }

    /**
     * Get the server's status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'running' => 'green',
            'starting' => 'yellow',
            'stopping' => 'orange',
            'stopped' => 'red',
            'crashed' => 'red',
            'installing' => 'blue',
            'suspended' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Scope servers by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope online servers.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope offline servers.
     */
    public function scopeOffline($query)
    {
        return $query->whereIn('status', ['stopped', 'crashed']);
    }

    /**
     * Scope suspended servers.
     */
    public function scopeSuspended($query)
    {
        return $query->where('suspended', true);
    }
}
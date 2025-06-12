<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_id',
        'type',
        'level',
        'message',
        'context',
        'source',
        'logged_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Get the server that owns this log entry.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the log level color.
     */
    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'debug' => 'gray',
            'info' => 'blue',
            'warning' => 'yellow',
            'error' => 'red',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the log type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'console' => 'terminal',
            'error' => 'exclamation-circle',
            'system' => 'cog',
            'audit' => 'eye',
            default => 'file-text',
        };
    }

    /**
     * Check if log is an error.
     */
    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical']);
    }

    /**
     * Check if log is a warning.
     */
    public function isWarning(): bool
    {
        return $this->level === 'warning';
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->logged_at->format('H:i:s');
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->logged_at->format('Y-m-d');
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by level.
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope errors only.
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical']);
    }

    /**
     * Scope warnings only.
     */
    public function scopeWarnings($query)
    {
        return $query->where('level', 'warning');
    }

    /**
     * Scope console logs.
     */
    public function scopeConsole($query)
    {
        return $query->where('type', 'console');
    }

    /**
     * Scope system logs.
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope audit logs.
     */
    public function scopeAudit($query)
    {
        return $query->where('type', 'audit');
    }

    /**
     * Scope recent logs.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('logged_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope today's logs.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('logged_at', today());
    }

    /**
     * Search logs by message content.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('message', 'like', "%{$search}%");
    }

    /**
     * Create a log entry.
     */
    public static function createEntry(
        int $serverId, 
        string $type, 
        string $level, 
        string $message, 
        array $context = [], 
        string $source = 'panel'
    ): self {
        return self::create([
            'server_id' => $serverId,
            'type' => $type,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'source' => $source,
            'logged_at' => now(),
        ]);
    }
}
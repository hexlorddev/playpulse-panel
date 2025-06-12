<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServerBackup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_id',
        'name',
        'uuid',
        'status',
        'type',
        'size',
        'compression',
        'storage_location',
        'file_path',
        'included_files',
        'excluded_files',
        'error_message',
        'started_at',
        'completed_at',
        'locked',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'included_files' => 'array',
        'excluded_files' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'locked' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($backup) {
            if (empty($backup->uuid)) {
                $backup->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the server that owns this backup.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Check if backup is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if backup failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if backup is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['pending', 'creating']);
    }

    /**
     * Check if backup can be deleted.
     */
    public function canDelete(): bool
    {
        return !$this->locked && !$this->isInProgress();
    }

    /**
     * Check if backup can be restored.
     */
    public function canRestore(): bool
    {
        return $this->isCompleted() && $this->file_path;
    }

    /**
     * Get human readable backup size.
     */
    public function getHumanSizeAttribute(): string
    {
        if (!$this->size) {
            return 'Unknown';
        }

        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get backup duration.
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $duration = $this->completed_at->diffInSeconds($this->started_at);
        
        if ($duration < 60) {
            return $duration . 's';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . 'm';
        } else {
            return round($duration / 3600, 1) . 'h';
        }
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'pending', 'creating' => 'yellow',
            'failed' => 'red',
            'restoring' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Mark backup as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark backup as completed.
     */
    public function markAsCompleted(int $size, string $filePath): void
    {
        $this->update([
            'status' => 'completed',
            'size' => $size,
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope completed backups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope failed backups.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope unlocked backups.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('locked', false);
    }

    /**
     * Scope recent backups.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
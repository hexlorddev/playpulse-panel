<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'fqdn',
        'daemon_port',
        'scheme',
        'secret',
        'memory',
        'memory_overallocate',
        'disk',
        'disk_overallocate',
        'cpu_limit',
        'upload_size',
        'location',
        'public',
        'behind_proxy',
        'maintenance_mode',
        'system_info',
        'last_ping',
        'resource_usage',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'public' => 'boolean',
        'behind_proxy' => 'boolean',
        'maintenance_mode' => 'boolean',
        'active' => 'boolean',
        'system_info' => 'array',
        'resource_usage' => 'array',
        'last_ping' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * Get the servers on this node.
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /**
     * Get the node's endpoint URL.
     */
    public function getEndpointAttribute(): string
    {
        return $this->scheme . '://' . $this->fqdn . ':' . $this->daemon_port;
    }

    /**
     * Check if the node is online.
     */
    public function isOnline(): bool
    {
        if (!$this->last_ping) {
            return false;
        }

        return $this->last_ping->diffInMinutes(now()) <= 5;
    }

    /**
     * Get memory allocation percentage.
     */
    public function getMemoryAllocationPercentage(): float
    {
        $allocated = $this->servers()->sum('memory_limit');
        return $this->memory > 0 ? ($allocated / $this->memory) * 100 : 0;
    }

    /**
     * Get disk allocation percentage.
     */
    public function getDiskAllocationPercentage(): float
    {
        $allocated = $this->servers()->sum('disk_limit');
        return $this->disk > 0 ? ($allocated / $this->disk) * 100 : 0;
    }

    /**
     * Get available memory.
     */
    public function getAvailableMemory(): int
    {
        $allocated = $this->servers()->sum('memory_limit');
        $overallocated = $this->memory * (1 + $this->memory_overallocate / 100);
        return max(0, $overallocated - $allocated);
    }

    /**
     * Get available disk space.
     */
    public function getAvailableDisk(): int
    {
        $allocated = $this->servers()->sum('disk_limit');
        $overallocated = $this->disk * (1 + $this->disk_overallocate / 100);
        return max(0, $overallocated - $allocated);
    }

    /**
     * Scope active nodes.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope public nodes.
     */
    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    /**
     * Scope online nodes.
     */
    public function scopeOnline($query)
    {
        return $query->where('last_ping', '>', now()->subMinutes(5));
    }

    /**
     * Check if node can accommodate a server with given resources.
     */
    public function canAccommodate(int $memory, int $disk): bool
    {
        return $this->getAvailableMemory() >= $memory 
            && $this->getAvailableDisk() >= $disk
            && $this->active
            && !$this->maintenance_mode;
    }
}
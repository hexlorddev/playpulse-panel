<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerFile extends Model
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
        'path',
        'type',
        'size',
        'modified_at',
        'permissions',
        'content_preview',
        'mime_type',
        'is_editable',
        'is_config',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'modified_at' => 'datetime',
        'is_editable' => 'boolean',
        'is_config' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the server that owns this file.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Check if file is a directory.
     */
    public function isDirectory(): bool
    {
        return $this->type === 'directory';
    }

    /**
     * Check if file is a regular file.
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Get human readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is editable based on type and size.
     */
    public function canEdit(): bool
    {
        if (!$this->is_editable || $this->isDirectory()) {
            return false;
        }

        // Don't allow editing files larger than 1MB
        if ($this->size > 1024 * 1024) {
            return false;
        }

        // Check if mime type is text-based
        $textMimeTypes = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'application/yaml',
            'text/yaml',
        ];

        return in_array($this->mime_type, $textMimeTypes) || 
               str_starts_with($this->mime_type, 'text/');
    }

    /**
     * Get file icon based on type/extension.
     */
    public function getIconAttribute(): string
    {
        if ($this->isDirectory()) {
            return 'folder';
        }

        $extension = strtolower($this->extension);
        
        return match($extension) {
            'txt', 'log', 'md' => 'file-text',
            'json', 'xml', 'yaml', 'yml' => 'file-code',
            'js', 'css', 'html', 'php' => 'file-code',
            'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'file-image',
            'mp3', 'wav', 'ogg' => 'file-audio',
            'mp4', 'avi', 'mov' => 'file-video',
            'zip', 'rar', '7z', 'tar', 'gz' => 'file-archive',
            'jar' => 'file-archive',
            default => 'file',
        };
    }

    /**
     * Scope files only.
     */
    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    /**
     * Scope directories only.
     */
    public function scopeDirectories($query)
    {
        return $query->where('type', 'directory');
    }

    /**
     * Scope editable files.
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Scope config files.
     */
    public function scopeConfig($query)
    {
        return $query->where('is_config', true);
    }

    /**
     * Scope by path.
     */
    public function scopeInPath($query, string $path)
    {
        return $query->where('path', 'like', $path . '%');
    }
}
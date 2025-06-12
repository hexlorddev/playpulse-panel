<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerTemplate extends Model
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
        'category',
        'game',
        'version',
        'description',
        'docker_image',
        'startup_command',
        'environment_variables',
        'configuration_files',
        'default_port',
        'min_memory',
        'max_memory',
        'min_cpu',
        'max_cpu',
        'min_disk',
        'max_disk',
        'port_mappings',
        'file_structure',
        'active',
        'featured',
        'icon',
        'install_script',
        'supported_versions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'environment_variables' => 'array',
        'configuration_files' => 'array',
        'port_mappings' => 'array',
        'file_structure' => 'array',
        'install_script' => 'array',
        'supported_versions' => 'array',
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * Get the servers using this template.
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /**
     * Scope active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope featured templates.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by game.
     */
    public function scopeByGame($query, string $game)
    {
        return $query->where('game', $game);
    }

    /**
     * Get the template's icon URL.
     */
    public function getIconUrlAttribute(): string
    {
        if ($this->icon && filter_var($this->icon, FILTER_VALIDATE_URL)) {
            return $this->icon;
        }

        return asset('images/templates/' . ($this->icon ?: 'default.png'));
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            'minecraft' => 'Minecraft',
            'source-games' => 'Source Games',
            'survival' => 'Survival Games',
            'voice' => 'Voice Servers',
            'databases' => 'Databases',
            'web' => 'Web Applications',
            'custom' => 'Custom Applications',
        ];
    }

    /**
     * Get available games.
     */
    public static function getGames(): array
    {
        return [
            'minecraft' => 'Minecraft',
            'csgo' => 'Counter-Strike: Global Offensive',
            'cs2' => 'Counter-Strike 2',
            'tf2' => 'Team Fortress 2',
            'gmod' => "Garry's Mod",
            'l4d2' => 'Left 4 Dead 2',
            'rust' => 'Rust',
            'ark' => 'ARK: Survival Evolved',
            'terraria' => 'Terraria',
            '7dtd' => '7 Days to Die',
            'valheim' => 'Valheim',
            'teamspeak' => 'TeamSpeak',
            'discord-bot' => 'Discord Bot',
            'mysql' => 'MySQL',
            'postgresql' => 'PostgreSQL',
            'nginx' => 'Nginx',
            'apache' => 'Apache',
            'nodejs' => 'Node.js',
            'python' => 'Python',
            'custom' => 'Custom Application',
        ];
    }
}
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerLog;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's servers with their current status
        $servers = $user->servers()
            ->with(['template', 'node'])
            ->latest()
            ->take(5)
            ->get();

        // Get server statistics
        $serverStats = [
            'total' => $user->servers()->count(),
            'online' => $user->servers()->online()->count(),
            'offline' => $user->servers()->offline()->count(),
            'suspended' => $user->servers()->suspended()->count(),
        ];

        // Get resource usage across all servers
        $resourceUsage = $this->calculateResourceUsage($user->servers);

        // Get recent activities/logs
        $recentActivities = ServerLog::whereIn('server_id', $user->servers()->pluck('id'))
            ->with('server')
            ->latest('logged_at')
            ->take(10)
            ->get();

        // Get user's subscription info
        $subscription = $user->subscription;
        $currentPlan = $user->currentPlan();

        // Calculate usage against plan limits
        $planUsage = null;
        if ($currentPlan) {
            $planUsage = [
                'servers' => [
                    'used' => $user->servers()->count(),
                    'limit' => $currentPlan->server_limit,
                    'percentage' => $currentPlan->server_limit > 0 
                        ? ($user->servers()->count() / $currentPlan->server_limit) * 100 
                        : 0
                ],
                'memory' => [
                    'used' => $user->servers()->sum('memory_limit'),
                    'limit' => $currentPlan->memory_limit * $user->servers()->count(),
                    'percentage' => $currentPlan->memory_limit > 0 
                        ? ($user->servers()->sum('memory_limit') / ($currentPlan->memory_limit * max(1, $user->servers()->count()))) * 100 
                        : 0
                ],
                'disk' => [
                    'used' => $user->servers()->sum('disk_limit'),
                    'limit' => $currentPlan->disk_limit * $user->servers()->count(),
                    'percentage' => $currentPlan->disk_limit > 0 
                        ? ($user->servers()->sum('disk_limit') / ($currentPlan->disk_limit * max(1, $user->servers()->count()))) * 100 
                        : 0
                ],
            ];
        }

        // Get system status
        $systemStatus = [
            'total_nodes' => Node::active()->count(),
            'online_nodes' => Node::online()->count(),
            'total_users_servers' => Server::count(),
            'system_load' => $this->getSystemLoad(),
        ];

        return view('dashboard.index', compact(
            'servers',
            'serverStats',
            'resourceUsage',
            'recentActivities',
            'subscription',
            'currentPlan',
            'planUsage',
            'systemStatus'
        ));
    }

    /**
     * Calculate resource usage across servers.
     */
    private function calculateResourceUsage($servers)
    {
        $totalMemory = $servers->sum('memory_limit');
        $totalDisk = $servers->sum('disk_limit');
        $totalCpu = $servers->avg('cpu_limit');

        $usedMemory = 0;
        $usedDisk = 0;
        $usedCpu = 0;

        foreach ($servers as $server) {
            $usage = $server->resource_usage;
            $usedMemory += $usage['memory'] ?? 0;
            $usedDisk += $usage['disk'] ?? 0;
            $usedCpu += $usage['cpu'] ?? 0;
        }

        $serverCount = $servers->count();

        return [
            'memory' => [
                'used' => $usedMemory,
                'total' => $totalMemory,
                'percentage' => $totalMemory > 0 ? ($usedMemory / $totalMemory) * 100 : 0
            ],
            'disk' => [
                'used' => $usedDisk,
                'total' => $totalDisk,
                'percentage' => $totalDisk > 0 ? ($usedDisk / $totalDisk) * 100 : 0
            ],
            'cpu' => [
                'used' => $serverCount > 0 ? $usedCpu / $serverCount : 0,
                'total' => $totalCpu,
                'percentage' => $serverCount > 0 ? ($usedCpu / $serverCount) : 0
            ],
        ];
    }

    /**
     * Get system load information.
     */
    private function getSystemLoad()
    {
        $nodes = Node::active()->get();
        
        if ($nodes->isEmpty()) {
            return null;
        }

        $totalMemoryUsage = 0;
        $totalCpuUsage = 0;
        $totalDiskUsage = 0;
        $nodeCount = 0;

        foreach ($nodes as $node) {
            if ($node->resource_usage) {
                $usage = $node->resource_usage;
                $totalMemoryUsage += $usage['memory'] ?? 0;
                $totalCpuUsage += $usage['cpu'] ?? 0;
                $totalDiskUsage += $usage['disk'] ?? 0;
                $nodeCount++;
            }
        }

        if ($nodeCount === 0) {
            return null;
        }

        return [
            'memory' => $totalMemoryUsage / $nodeCount,
            'cpu' => $totalCpuUsage / $nodeCount,
            'disk' => $totalDiskUsage / $nodeCount,
        ];
    }

    /**
     * Get real-time dashboard data via AJAX.
     */
    public function realTimeData()
    {
        $user = Auth::user();
        
        $data = [
            'servers' => $user->servers()
                ->select('id', 'name', 'status', 'resource_usage', 'player_count', 'max_players')
                ->get()
                ->map(function ($server) {
                    return [
                        'id' => $server->id,
                        'name' => $server->name,
                        'status' => $server->status,
                        'status_color' => $server->status_color,
                        'resource_usage' => $server->resource_usage,
                        'memory_percentage' => $server->getMemoryUsagePercentage(),
                        'cpu_percentage' => $server->getCpuUsagePercentage(),
                        'disk_percentage' => $server->getDiskUsagePercentage(),
                        'player_count' => $server->player_count,
                        'max_players' => $server->max_players,
                    ];
                }),
            'stats' => [
                'total' => $user->servers()->count(),
                'online' => $user->servers()->online()->count(),
                'offline' => $user->servers()->offline()->count(),
                'suspended' => $user->servers()->suspended()->count(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($data);
    }
}
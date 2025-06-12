<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerTemplate;
use App\Models\Node;
use App\Models\ServerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServerController extends Controller
{
    /**
     * Display a listing of the user's servers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->servers()->with(['template', 'node']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('game')) {
            $query->where('game', $request->game);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $query->orderBy($sortBy, $sortDirection);

        $servers = $query->paginate(12);

        $stats = [
            'total' => $user->servers()->count(),
            'online' => $user->servers()->online()->count(),
            'offline' => $user->servers()->offline()->count(),
            'suspended' => $user->servers()->suspended()->count(),
        ];

        return view('dashboard.servers.index', compact('servers', 'stats'));
    }

    /**
     * Show the form for creating a new server.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user can create more servers
        if (!$user->canCreateServer()) {
            return redirect()->route('dashboard.servers.index')
                ->with('error', 'You have reached your server limit. Please upgrade your plan.');
        }

        $templates = ServerTemplate::active()
            ->orderBy('featured', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $nodes = Node::active()
            ->public()
            ->where('maintenance_mode', false)
            ->get();

        return view('dashboard.servers.create', compact('templates', 'nodes'));
    }

    /**
     * Store a newly created server.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user can create more servers
        if (!$user->canCreateServer()) {
            return redirect()->route('dashboard.servers.index')
                ->with('error', 'You have reached your server limit.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:server_templates,id',
            'memory' => 'required|integer|min:512|max:16384',
            'disk' => 'required|integer|min:1024|max:102400',
            'cpu' => 'required|integer|min:50|max:400',
            'node_id' => 'nullable|exists:nodes,id',
        ]);

        $template = ServerTemplate::findOrFail($request->template_id);
        
        // Validate resource limits against template and plan
        $plan = $user->currentPlan();
        if ($plan) {
            if ($request->memory > $plan->memory_limit) {
                return back()->withErrors(['memory' => 'Memory exceeds your plan limit.']);
            }
            if ($request->disk > $plan->disk_limit) {
                return back()->withErrors(['disk' => 'Disk space exceeds your plan limit.']);
            }
            if ($request->cpu > $plan->cpu_limit) {
                return back()->withErrors(['cpu' => 'CPU limit exceeds your plan limit.']);
            }
        }

        // Auto-select node if not specified
        if (!$request->node_id) {
            $node = Node::active()
                ->public()
                ->where('maintenance_mode', false)
                ->get()
                ->first(function ($node) use ($request) {
                    return $node->canAccommodate($request->memory, $request->disk);
                });

            if (!$node) {
                return back()->withErrors(['node_id' => 'No available nodes can accommodate this server.']);
            }
            
            $request->merge(['node_id' => $node->id]);
        }

        // Find available port
        $port = $this->findAvailablePort($template->default_port);

        $server = Server::create([
            'name' => $request->name,
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'server_template_id' => $template->id,
            'status' => 'installing',
            'game' => $template->game,
            'port' => $port,
            'memory_limit' => $request->memory,
            'cpu_limit' => $request->cpu,
            'disk_limit' => $request->disk,
            'node_id' => $request->node_id,
            'startup_command' => $template->startup_command,
            'environment_variables' => $template->environment_variables,
            'configuration' => $template->configuration_files,
        ]);

        // Log server creation
        ServerLog::createEntry(
            $server->id,
            'system',
            'info',
            'Server created and installation started',
            ['user_id' => $user->id],
            'panel'
        );

        // TODO: Queue server installation job
        
        return redirect()->route('dashboard.servers.show', $server)
            ->with('success', 'Server created successfully! Installation will begin shortly.');
    }

    /**
     * Display the specified server.
     */
    public function show(Server $server)
    {
        Gate::authorize('view', $server);
        
        $server->load(['template', 'node', 'logs' => function ($query) {
            $query->latest('logged_at')->take(100);
        }]);

        $stats = [
            'uptime' => $this->calculateUptime($server),
            'memory_usage' => $server->getMemoryUsagePercentage(),
            'cpu_usage' => $server->getCpuUsagePercentage(),
            'disk_usage' => $server->getDiskUsagePercentage(),
            'network_usage' => $server->resource_usage['network_in'] ?? 0,
        ];

        return view('dashboard.servers.show', compact('server', 'stats'));
    }

    /**
     * Show server console.
     */
    public function console(Server $server)
    {
        Gate::authorize('view', $server);
        
        $logs = $server->logs()
            ->console()
            ->latest('logged_at')
            ->take(500)
            ->get()
            ->reverse();

        return view('dashboard.servers.console', compact('server', 'logs'));
    }

    /**
     * Show server settings.
     */
    public function settings(Server $server)
    {
        Gate::authorize('update', $server);
        
        return view('dashboard.servers.settings', compact('server'));
    }

    /**
     * Update server settings.
     */
    public function updateSettings(Request $request, Server $server)
    {
        Gate::authorize('update', $server);

        $request->validate([
            'name' => 'required|string|max:255',
            'memory_limit' => 'required|integer|min:512',
            'cpu_limit' => 'required|integer|min:50',
            'disk_limit' => 'required|integer|min:1024',
            'auto_start' => 'boolean',
        ]);

        $server->update($request->only([
            'name',
            'memory_limit',
            'cpu_limit', 
            'disk_limit',
            'auto_start'
        ]));

        ServerLog::createEntry(
            $server->id,
            'system',
            'info',
            'Server settings updated',
            ['changes' => $server->getChanges()],
            'panel'
        );

        return back()->with('success', 'Server settings updated successfully.');
    }

    /**
     * Start server.
     */
    public function start(Server $server)
    {
        Gate::authorize('update', $server);

        if ($server->status !== 'stopped') {
            return back()->with('error', 'Server is not in a stopped state.');
        }

        $server->update(['status' => 'starting']);
        
        ServerLog::createEntry(
            $server->id,
            'system',
            'info',
            'Server start requested',
            [],
            'panel'
        );

        // TODO: Queue server start job

        return back()->with('success', 'Server start command sent.');
    }

    /**
     * Stop server.
     */
    public function stop(Server $server)
    {
        Gate::authorize('update', $server);

        if (!in_array($server->status, ['running', 'starting'])) {
            return back()->with('error', 'Server is not running.');
        }

        $server->update(['status' => 'stopping']);
        
        ServerLog::createEntry(
            $server->id,
            'system',
            'info',
            'Server stop requested',
            [],
            'panel'
        );

        // TODO: Queue server stop job

        return back()->with('success', 'Server stop command sent.');
    }

    /**
     * Restart server.
     */
    public function restart(Server $server)
    {
        Gate::authorize('update', $server);

        if ($server->status === 'installing') {
            return back()->with('error', 'Cannot restart server during installation.');
        }

        $server->update(['status' => 'stopping']);
        
        ServerLog::createEntry(
            $server->id,
            'system',
            'info',
            'Server restart requested',
            [],
            'panel'
        );

        // TODO: Queue server restart job

        return back()->with('success', 'Server restart command sent.');
    }

    /**
     * Delete server.
     */
    public function destroy(Server $server)
    {
        Gate::authorize('delete', $server);

        $serverName = $server->name;
        
        // TODO: Queue server deletion job
        
        $server->delete();

        return redirect()->route('dashboard.servers.index')
            ->with('success', "Server '{$serverName}' has been deleted.");
    }

    /**
     * Find an available port.
     */
    private function findAvailablePort(int $startPort = 25565): int
    {
        $port = $startPort;
        
        while (Server::where('port', $port)->exists()) {
            $port++;
        }
        
        return $port;
    }

    /**
     * Calculate server uptime.
     */
    private function calculateUptime(Server $server): ?string
    {
        if (!$server->last_activity || $server->status !== 'running') {
            return null;
        }

        $uptime = now()->diffInSeconds($server->last_activity);
        
        if ($uptime < 60) {
            return $uptime . ' seconds';
        } elseif ($uptime < 3600) {
            return round($uptime / 60) . ' minutes';
        } elseif ($uptime < 86400) {
            return round($uptime / 3600, 1) . ' hours';
        } else {
            return round($uptime / 86400, 1) . ' days';
        }
    }
}
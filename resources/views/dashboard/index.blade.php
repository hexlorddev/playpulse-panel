@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-gray-600 mt-1">
                    Here's what's happening with your servers today.
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ now()->format('g:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Servers -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-server text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Servers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $serverStats['total'] }}</p>
                </div>
            </div>
        </div>

        <!-- Online Servers -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-play-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Online</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $serverStats['online'] }}</p>
                </div>
            </div>
            @if($serverStats['online'] > 0)
                <div class="mt-2">
                    <span class="inline-flex items-center text-xs text-green-600">
                        <span class="pulse-animation w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        Running smoothly
                    </span>
                </div>
            @endif
        </div>

        <!-- Offline Servers -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <i class="fas fa-stop-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Offline</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $serverStats['offline'] }}</p>
                </div>
            </div>
        </div>

        <!-- Resource Usage -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-microchip text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">CPU Usage</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($resourceUsage['cpu']['percentage'], 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Servers Overview -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Your Servers</h3>
                    <a href="{{ route('dashboard.servers.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create Server
                    </a>
                </div>

                @if($servers->count() > 0)
                    <div class="space-y-4">
                        @foreach($servers as $server)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                            <i class="fas fa-cube text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $server->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $server->template->name }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($server->status === 'running') bg-green-100 text-green-800
                                            @elseif($server->status === 'stopped') bg-red-100 text-red-800
                                            @elseif($server->status === 'starting') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($server->status === 'running')
                                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 pulse-animation"></span>
                                            @endif
                                            {{ ucfirst($server->status) }}
                                        </span>

                                        <!-- Resource Usage -->
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Memory</p>
                                            <p class="text-sm font-medium">{{ number_format($server->getMemoryUsagePercentage(), 1) }}%</p>
                                        </div>

                                        <!-- Actions -->
                                        <a href="{{ route('dashboard.servers.show', $server) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>

                                @if($server->game === 'minecraft' && $server->status === 'running')
                                    <div class="mt-3 flex items-center text-sm text-gray-600">
                                        <i class="fas fa-users mr-2"></i>
                                        {{ $server->player_count }}/{{ $server->max_players }} players online
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($servers->count() >= 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('dashboard.servers.index') }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                View all servers →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-server text-gray-400 text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">No servers yet</h4>
                        <p class="text-gray-600 mb-4">Create your first game server to get started.</p>
                        <a href="{{ route('dashboard.servers.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Create Your First Server
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Resource Usage -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resource Usage</h3>
                
                <div class="space-y-4">
                    <!-- Memory -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Memory</span>
                            <span class="font-medium">{{ number_format($resourceUsage['memory']['percentage'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, $resourceUsage['memory']['percentage']) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ number_format($resourceUsage['memory']['used'] / 1024, 1) }}GB / {{ number_format($resourceUsage['memory']['total'] / 1024, 1) }}GB
                        </p>
                    </div>

                    <!-- CPU -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">CPU</span>
                            <span class="font-medium">{{ number_format($resourceUsage['cpu']['percentage'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, $resourceUsage['cpu']['percentage']) }}%"></div>
                        </div>
                    </div>

                    <!-- Disk -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Disk</span>
                            <span class="font-medium">{{ number_format($resourceUsage['disk']['percentage'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, $resourceUsage['disk']['percentage']) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ number_format($resourceUsage['disk']['used'] / 1024, 1) }}GB / {{ number_format($resourceUsage['disk']['total'] / 1024, 1) }}GB
                        </p>
                    </div>
                </div>
            </div>

            <!-- Plan Information -->
            @if($currentPlan)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Plan</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plan</span>
                            <span class="font-medium">{{ $currentPlan->name }}</span>
                        </div>
                        
                        @if($planUsage)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Servers</span>
                                <span class="font-medium">{{ $planUsage['servers']['used'] }}/{{ $planUsage['servers']['limit'] }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Price</span>
                            <span class="font-medium">${{ $currentPlan->price }}/{{ $currentPlan->billing_period }}</span>
                        </div>
                    </div>
                    
                    @if($subscription && $subscription->current_period_end)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                Renews on {{ $subscription->current_period_end->format('M j, Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Recent Activity -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                
                @if($recentActivities->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentActivities->take(5) as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mt-2 flex-shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">{{ $activity->message }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $activity->server->name }} • {{ $activity->logged_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 text-sm">No recent activity</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh dashboard data every 30 seconds
    setInterval(function() {
        fetch('{{ route('dashboard.realtime-data') }}')
            .then(response => response.json())
            .then(data => {
                // Update server statuses and metrics
                updateDashboardData(data);
            })
            .catch(error => {
                console.error('Failed to fetch real-time data:', error);
            });
    }, 30000);

    function updateDashboardData(data) {
        // Update server count badges
        const totalServers = document.querySelector('[data-stat="total"]');
        if (totalServers) totalServers.textContent = data.stats.total;
        
        const onlineServers = document.querySelector('[data-stat="online"]');
        if (onlineServers) onlineServers.textContent = data.stats.online;
        
        const offlineServers = document.querySelector('[data-stat="offline"]');
        if (offlineServers) offlineServers.textContent = data.stats.offline;
        
        // Update individual server statuses
        data.servers.forEach(server => {
            const serverElement = document.querySelector(`[data-server-id="${server.id}"]`);
            if (serverElement) {
                // Update status badge
                const statusBadge = serverElement.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${getStatusClasses(server.status)}`;
                    statusBadge.textContent = server.status.charAt(0).toUpperCase() + server.status.slice(1);
                }
                
                // Update resource usage
                const memoryUsage = serverElement.querySelector('.memory-usage');
                if (memoryUsage) {
                    memoryUsage.textContent = `${server.memory_percentage.toFixed(1)}%`;
                }
            }
        });
    }

    function getStatusClasses(status) {
        switch(status) {
            case 'running': return 'bg-green-100 text-green-800';
            case 'stopped': return 'bg-red-100 text-red-800';
            case 'starting': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
</script>
@endpush
@endsection
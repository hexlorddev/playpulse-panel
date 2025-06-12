<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'PlayPulse') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|orbitron:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #00CFFF;
            --secondary-color: #FF2D55;
            --dark-bg: #0D0D0D;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --neon-glow: 0 0 20px rgba(0, 207, 255, 0.5);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0D0D0D 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        .font-orbitron {
            font-family: 'Orbitron', monospace;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #0D0D0D 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .glass-sidebar {
            background: rgba(13, 13, 13, 0.9);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 207, 255, 0.3);
        }
        
        .neon-border {
            border: 1px solid var(--primary-color);
            box-shadow: var(--neon-glow);
        }
        
        .neon-text {
            color: var(--primary-color);
            text-shadow: 0 0 10px rgba(0, 207, 255, 0.8);
        }
        
        .server-status-online {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }
        
        .server-status-offline {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
        }
        
        .server-status-starting {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .cyber-glow {
            position: relative;
            overflow: hidden;
        }
        
        .cyber-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 207, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .cyber-glow:hover::before {
            left: 100%;
        }
        
        .sidebar-item {
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .sidebar-item:hover::before,
        .sidebar-item.active::before {
            transform: scaleY(1);
        }
        
        .sidebar-item:hover {
            background: rgba(0, 207, 255, 0.1);
            color: var(--primary-color);
            transform: translateX(8px);
        }
        
        .sidebar-item.active {
            background: rgba(0, 207, 255, 0.15);
            color: var(--primary-color);
            border-right: 3px solid var(--primary-color);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #00a3cc;
        }
        
        /* Animated Background */
        .animated-bg {
            background: linear-gradient(-45deg, #0D0D0D, #1a1a2e, #16213e, #0f3460);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Matrix-style data visualization */
        .matrix-grid {
            background-image: 
                linear-gradient(rgba(0, 207, 255, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 207, 255, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        
        /* Holographic effect */
        .holographic {
            background: linear-gradient(45deg, transparent 30%, rgba(0, 207, 255, 0.1) 50%, transparent 70%);
            background-size: 20px 20px;
            animation: hologram 3s linear infinite;
        }
        
        @keyframes hologram {
            0% { background-position: 0px 0px; }
            100% { background-position: 40px 40px; }
        }
        
        /* Terminal-style console */
        .terminal-console {
            background: #000;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            border: 1px solid var(--primary-color);
            box-shadow: inset 0 0 20px rgba(0, 255, 0, 0.1);
        }
        
        /* Futuristic buttons */
        .cyber-button {
            position: relative;
            background: linear-gradient(45deg, rgba(0, 207, 255, 0.1), rgba(0, 207, 255, 0.3));
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .cyber-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .cyber-button:hover::before {
            left: 100%;
        }
        
        .cyber-button:hover {
            background: linear-gradient(45deg, rgba(0, 207, 255, 0.2), rgba(0, 207, 255, 0.5));
            box-shadow: 0 0 30px rgba(0, 207, 255, 0.5);
            transform: translateY(-2px);
        }
        
        /* Loading animations */
        .loading-pulse {
            animation: loadingPulse 1.5s ease-in-out infinite;
        }
        
        @keyframes loadingPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Particle effect background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0; }
            50% { transform: translateY(-100px) rotate(180deg); opacity: 1; }
        }
        
        /* Stats cards with glow effect */
        .stat-card {
            background: rgba(0, 207, 255, 0.05);
            border: 1px solid rgba(0, 207, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            background: rgba(0, 207, 255, 0.1);
            box-shadow: 0 0 30px rgba(0, 207, 255, 0.3);
            transform: translateY(-5px);
        }
    </style>

    @stack('styles')
</head>
<body class="h-full animated-bg text-white">
    <!-- Particle Background -->
    <div class="particles" id="particles"></div>
    
    <div id="app" class="min-h-full">
        <!-- Futuristic Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 glass-sidebar">
            <!-- Logo Section with Custom SVG -->
            <div class="flex h-20 items-center justify-center px-6 border-b border-cyan-500/30">
                <div class="flex items-center space-x-3">
                    <!-- Custom Logo SVG -->
                    <svg width="48" height="24" viewBox="0 0 240 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="transform scale-75">
                        <rect x="0" y="0" width="240" height="60" fill="transparent"/>
                        <path d="M10 30 L30 30 L35 20 L45 40 L50 30 L60 30" stroke="#00CFFF" stroke-width="4" fill="none"/>
                        <circle cx="20" cy="30" r="5" fill="#FF2D55"/>
                    </svg>
                    <div>
                        <h1 class="text-xl font-bold font-orbitron neon-text">
                            PlayPulse
                        </h1>
                        <p class="text-xs text-cyan-400 font-orbitron">HOSTING</p>
                    </div>
                </div>
            </div>

            <!-- AI Status Indicator -->
            <div class="mx-4 mt-4 mb-6">
                <div class="glass-card rounded-lg p-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full pulse-animation"></div>
                        <span class="text-xs text-green-400 font-medium">AI Assistant Online</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        System optimization: <span class="text-cyan-400">97.3%</span>
                    </div>
                </div>
            </div>

            <!-- Enhanced Navigation -->
            <nav class="mt-4 px-4">
                <div class="space-y-1">
                    <a href="{{ route('dashboard.index') }}" 
                       class="sidebar-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-chart-line mr-3"></i>
                        <span class="font-medium">Command Center</span>
                        <span class="ml-auto text-xs bg-cyan-500/20 px-2 py-1 rounded-full">NEW</span>
                    </a>
                    
                    <a href="{{ route('dashboard.servers.index') }}" 
                       class="sidebar-item {{ request()->routeIs('dashboard.servers.*') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-server mr-3"></i>
                        <span class="font-medium">Servers</span>
                        @if(auth()->user()->servers()->count() > 0)
                            <span class="ml-auto bg-cyan-500 text-black text-xs px-2 py-1 rounded-full font-bold">
                                {{ auth()->user()->servers()->count() }}
                            </span>
                        @endif
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-brain mr-3"></i>
                        <span class="font-medium">AI Optimizer</span>
                        <span class="ml-auto text-xs bg-gradient-to-r from-purple-500 to-pink-500 px-2 py-1 rounded-full">AI</span>
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-folder mr-3"></i>
                        <span class="font-medium">File Matrix</span>
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-database mr-3"></i>
                        <span class="font-medium">Data Cores</span>
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-shield-alt mr-3"></i>
                        <span class="font-medium">Security Grid</span>
                        <span class="ml-auto w-2 h-2 bg-green-400 rounded-full pulse-animation"></span>
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-chart-area mr-3"></i>
                        <span class="font-medium">Analytics Hub</span>
                    </a>
                    
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                        <i class="fas fa-backup mr-3"></i>
                        <span class="font-medium">Backup Vault</span>
                    </a>
                </div>

                <!-- Advanced Features Section -->
                <div class="mt-8 pt-6 border-t border-cyan-500/30">
                    <div class="px-4 mb-4">
                        <p class="text-xs font-medium text-cyan-400 uppercase tracking-wider font-orbitron">Advanced Systems</p>
                    </div>
                    <div class="space-y-1">
                        <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                            <i class="fas fa-network-wired mr-3"></i>
                            <span class="font-medium">Node Network</span>
                        </a>
                        
                        <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                            <i class="fas fa-rocket mr-3"></i>
                            <span class="font-medium">Performance Boost</span>
                        </a>
                        
                        <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                            <i class="fas fa-cog mr-3"></i>
                            <span class="font-medium">System Config</span>
                        </a>
                    </div>
                </div>

                <!-- User Section -->
                <div class="mt-8 pt-6 border-t border-cyan-500/30">
                    <div class="space-y-1">
                        <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                            <i class="fas fa-credit-card mr-3"></i>
                            <span class="font-medium">Billing Portal</span>
                        </a>
                        
                        <a href="#" class="sidebar-item flex items-center px-4 py-3 text-white rounded-lg cyber-glow">
                            <i class="fas fa-life-ring mr-3"></i>
                            <span class="font-medium">Support Hub</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- System Status Footer -->
            <div class="absolute bottom-4 left-4 right-4">
                <div class="glass-card rounded-lg p-3">
                    <div class="text-xs text-gray-400 space-y-1">
                        <div class="flex justify-between">
                            <span>System Load:</span>
                            <span class="text-green-400">12%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Uptime:</span>
                            <span class="text-cyan-400">99.9%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>AI Status:</span>
                            <span class="text-green-400">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="pl-64">
            <!-- Futuristic Top Navigation -->
            <header class="glass-card border-b border-cyan-500/30">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-xl font-semibold text-white font-orbitron">
                            @yield('page-title', 'Command Center')
                        </h2>
                        <!-- Real-time indicators -->
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-400 rounded-full pulse-animation"></div>
                                <span class="text-xs text-green-400">Live</span>
                            </div>
                            <div class="text-xs text-gray-400">|</div>
                            <div class="text-xs text-cyan-400">
                                <i class="fas fa-bolt mr-1"></i>
                                Ultra Performance Mode
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Global Search -->
                        <div class="relative">
                            <input type="text" 
                                   placeholder="Search servers, files, logs..." 
                                   class="bg-black/50 border border-cyan-500/50 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400 w-64">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>

                        <!-- AI Assistant Toggle -->
                        <button class="cyber-button px-3 py-2 rounded-lg">
                            <i class="fas fa-robot mr-2"></i>
                            AI Assistant
                        </button>

                        <!-- Notifications with animation -->
                        <button class="relative p-2 text-gray-400 hover:text-cyan-400 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 block h-4 w-4 rounded-full bg-red-500 text-xs text-white flex items-center justify-center pulse-animation">3</span>
                        </button>

                        <!-- User Menu with avatar -->
                        <div class="relative">
                            <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-cyan-500/10 transition-colors" onclick="toggleUserMenu()">
                                <div class="relative">
                                    <img class="h-8 w-8 rounded-full border-2 border-cyan-500" 
                                         src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&color=00CFFF&background=0D0D0D' }}" 
                                         alt="{{ auth()->user()->name }}">
                                    <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900"></div>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-400">Elite User</div>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>

                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 glass-card rounded-lg border border-cyan-500/30 py-2 z-50">
                                <div class="px-4 py-2 border-b border-cyan-500/30">
                                    <p class="text-xs text-gray-400">Signed in as</p>
                                    <p class="text-sm font-medium text-white">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                                    <i class="fas fa-user mr-2"></i>
                                    Profile Settings
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                                    <i class="fas fa-cog mr-2"></i>
                                    Preferences
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                                    <i class="fas fa-key mr-2"></i>
                                    Security
                                </a>
                                <div class="border-t border-cyan-500/30 mt-2"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="px-6 py-8">
                @if(session('success'))
                    <div class="mb-6 p-4 glass-card border border-green-500/50 rounded-lg animate-slide-down">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3 text-xl"></i>
                            <p class="text-green-400 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 glass-card border border-red-500/50 rounded-lg animate-slide-down">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-3 text-xl"></i>
                            <p class="text-red-400 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const button = event.target.closest('button');
            
            if (!button || !button.getAttribute('onclick')?.includes('toggleUserMenu')) {
                menu.classList.add('hidden');
            }
        });

        // Initialize particles and other effects
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });

        // Cyber-style loading effect for buttons
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('cyber-button')) {
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                button.disabled = true;
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
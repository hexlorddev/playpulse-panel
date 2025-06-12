import './bootstrap';
import Alpine from 'alpinejs';

// Start Alpine.js
window.Alpine = Alpine;
Alpine.start();

// PlayPulse Panel JavaScript

// Global utilities
window.PlayPulse = {
    // Format bytes to human readable
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },

    // Format time duration
    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    },

    // Show notification
    notify(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        notification.className += ` ${colors[type]}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    },

    // AJAX helper
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        };

        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    },

    // Server control actions
    async serverAction(serverId, action) {
        try {
            const response = await this.request(`/dashboard/servers/${serverId}/${action}`, {
                method: 'POST'
            });
            
            this.notify(`Server ${action} command sent successfully`, 'success');
            
            // Refresh page after a short delay to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } catch (error) {
            this.notify(`Failed to ${action} server: ${error.message}`, 'error');
        }
    },

    // Real-time updates
    startRealTimeUpdates() {
        if (window.location.pathname.includes('/dashboard')) {
            setInterval(async () => {
                try {
                    const data = await this.request('/dashboard/realtime-data');
                    this.updateDashboardData(data);
                } catch (error) {
                    console.error('Failed to fetch real-time data:', error);
                }
            }, 30000); // Update every 30 seconds
        }
    },

    // Update dashboard with real-time data
    updateDashboardData(data) {
        // Update server statuses
        data.servers.forEach(server => {
            const serverElement = document.querySelector(`[data-server-id="${server.id}"]`);
            if (serverElement) {
                // Update status badge
                const statusBadge = serverElement.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `status-badge ${this.getStatusClasses(server.status)}`;
                    statusBadge.textContent = server.status.charAt(0).toUpperCase() + server.status.slice(1);
                }
                
                // Update resource usage
                const memoryUsage = serverElement.querySelector('.memory-usage');
                if (memoryUsage) {
                    memoryUsage.textContent = `${server.memory_percentage.toFixed(1)}%`;
                }
                
                // Update player count for Minecraft servers
                const playerCount = serverElement.querySelector('.player-count');
                if (playerCount && server.player_count !== undefined) {
                    playerCount.textContent = `${server.player_count}/${server.max_players} players`;
                }
            }
        });

        // Update statistics
        Object.entries(data.stats).forEach(([key, value]) => {
            const statElement = document.querySelector(`[data-stat="${key}"]`);
            if (statElement) {
                statElement.textContent = value;
            }
        });
    },

    // Get CSS classes for server status
    getStatusClasses(status) {
        const classes = {
            running: 'bg-green-100 text-green-800',
            stopped: 'bg-red-100 text-red-800',
            starting: 'bg-yellow-100 text-yellow-800',
            stopping: 'bg-orange-100 text-orange-800',
            crashed: 'bg-red-100 text-red-800',
            installing: 'bg-blue-100 text-blue-800',
            suspended: 'bg-purple-100 text-purple-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }
};

// Initialize real-time updates
document.addEventListener('DOMContentLoaded', () => {
    PlayPulse.startRealTimeUpdates();
});

// Alpine.js components
document.addEventListener('alpine:init', () => {
    // Server management component
    Alpine.data('serverManager', () => ({
        loading: false,
        
        async executeAction(serverId, action) {
            this.loading = true;
            try {
                await PlayPulse.serverAction(serverId, action);
            } finally {
                this.loading = false;
            }
        }
    }));

    // File manager component
    Alpine.data('fileManager', () => ({
        currentPath: '/',
        selectedFiles: [],
        
        selectFile(file) {
            if (this.selectedFiles.includes(file)) {
                this.selectedFiles = this.selectedFiles.filter(f => f !== file);
            } else {
                this.selectedFiles.push(file);
            }
        },
        
        isSelected(file) {
            return this.selectedFiles.includes(file);
        },
        
        clearSelection() {
            this.selectedFiles = [];
        }
    }));

    // Console component
    Alpine.data('serverConsole', () => ({
        command: '',
        logs: [],
        autoScroll: true,
        
        async sendCommand(serverId) {
            if (!this.command.trim()) return;
            
            try {
                await PlayPulse.request(`/dashboard/servers/${serverId}/console/command`, {
                    method: 'POST',
                    body: JSON.stringify({ command: this.command })
                });
                
                this.command = '';
                PlayPulse.notify('Command sent successfully', 'success');
            } catch (error) {
                PlayPulse.notify(`Failed to send command: ${error.message}`, 'error');
            }
        },
        
        scrollToBottom() {
            if (this.autoScroll) {
                const console = document.getElementById('console-output');
                if (console) {
                    console.scrollTop = console.scrollHeight;
                }
            }
        }
    }));
});

// Global event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Confirm delete actions
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('confirm-delete')) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    // Loading states for buttons
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-loading')) {
            e.target.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            e.target.disabled = true;
        }
    });
});

// Export for global use
window.PlayPulse = PlayPulse;
<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own servers
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Server $server): bool
    {
        // User can view their own server or admins can view any server
        return $user->id === $server->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check if user has an active subscription and can create more servers
        return $user->hasActiveSubscription() && $user->canCreateServer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Server $server): bool
    {
        // User can update their own server (if not suspended) or admins can update any server
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->id !== $server->user_id) {
            return false;
        }

        // Check if server is suspended
        if ($server->isSuspended()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Server $server): bool
    {
        // User can delete their own server or admins can delete any server
        return $user->id === $server->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Server $server): bool
    {
        // Only admins can restore servers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Server $server): bool
    {
        // Only admins can force delete servers
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can start/stop/restart the server.
     */
    public function control(User $user, Server $server): bool
    {
        // User can control their own server (if not suspended) or admins can control any server
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->id !== $server->user_id) {
            return false;
        }

        // Check if server is suspended
        if ($server->isSuspended()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can access server console.
     */
    public function console(User $user, Server $server): bool
    {
        return $this->control($user, $server);
    }

    /**
     * Determine whether the user can access server files.
     */
    public function files(User $user, Server $server): bool
    {
        return $this->control($user, $server);
    }

    /**
     * Determine whether the user can manage server backups.
     */
    public function backups(User $user, Server $server): bool
    {
        return $this->view($user, $server);
    }

    /**
     * Determine whether the user can create server backups.
     */
    public function createBackup(User $user, Server $server): bool
    {
        // Check backup limit
        $plan = $user->currentPlan();
        if ($plan && $server->backups()->count() >= $plan->backup_limit) {
            return false;
        }

        return $this->control($user, $server);
    }

    /**
     * Determine whether the user can view server databases.
     */
    public function databases(User $user, Server $server): bool
    {
        return $this->view($user, $server);
    }

    /**
     * Determine whether the user can create server databases.
     */
    public function createDatabase(User $user, Server $server): bool
    {
        // Check database limit
        $plan = $user->currentPlan();
        if ($plan && $server->database_limit <= 0) {
            return false;
        }

        return $this->control($user, $server);
    }

    /**
     * Determine whether the user can view server statistics.
     */
    public function statistics(User $user, Server $server): bool
    {
        return $this->view($user, $server);
    }
}
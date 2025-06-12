<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('server_template_id')->constrained()->onDelete('restrict');
            $table->string('status')->default('installing'); // installing, starting, running, stopping, stopped, crashed, suspended
            $table->string('game')->nullable(); // minecraft, csgo, rust, etc.
            $table->integer('port')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('environment_variables')->nullable();
            $table->text('startup_command')->nullable();
            $table->integer('memory_limit')->default(1024); // MB
            $table->integer('cpu_limit')->default(100); // percentage
            $table->integer('disk_limit')->default(5120); // MB
            $table->integer('database_limit')->default(0);
            $table->integer('backup_limit')->default(3);
            $table->boolean('suspended')->default(false);
            $table->text('suspension_reason')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->json('resource_usage')->nullable(); // current CPU, RAM, disk usage
            $table->string('container_id')->nullable();
            $table->string('node_id')->nullable();
            $table->integer('player_count')->default(0);
            $table->integer('max_players')->default(20);
            $table->json('configuration')->nullable(); // server-specific config
            $table->boolean('auto_start')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('uuid');
            $table->index('node_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
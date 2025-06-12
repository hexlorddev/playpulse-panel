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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Node display name
            $table->string('fqdn'); // node.example.com
            $table->integer('daemon_port')->default(8080); // PlayPulse daemon port
            $table->string('scheme')->default('https'); // http or https
            $table->text('secret'); // Authentication secret
            $table->integer('memory')->default(0); // Total memory in MB
            $table->integer('memory_overallocate')->default(0); // % overallocation allowed
            $table->integer('disk')->default(0); // Total disk in MB
            $table->integer('disk_overallocate')->default(0); // % overallocation allowed
            $table->integer('cpu_limit')->default(100); // CPU limit percentage
            $table->string('upload_size')->default('100M'); // Max upload size
            $table->string('location')->nullable(); // Geographic location
            $table->boolean('public')->default(true); // Is public for server creation
            $table->boolean('behind_proxy')->default(false); // Behind reverse proxy
            $table->boolean('maintenance_mode')->default(false);
            $table->json('system_info')->nullable(); // OS, kernel, etc.
            $table->timestamp('last_ping')->nullable(); // Last successful ping
            $table->json('resource_usage')->nullable(); // Current usage stats
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('active');
            $table->index('public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
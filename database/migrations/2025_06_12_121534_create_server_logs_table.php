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
        Schema::create('server_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('console'); // console, error, system, audit
            $table->string('level')->default('info'); // debug, info, warning, error, critical
            $table->text('message'); // Log message content
            $table->json('context')->nullable(); // Additional context data
            $table->string('source')->nullable(); // server, panel, system
            $table->timestamp('logged_at'); // When the event occurred
            $table->timestamps();
            
            $table->index(['server_id', 'type', 'logged_at']);
            $table->index(['server_id', 'level']);
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_logs');
    }
};
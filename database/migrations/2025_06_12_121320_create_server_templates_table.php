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
        Schema::create('server_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Minecraft Vanilla, Paper, Spigot, etc.
            $table->string('slug')->unique(); // minecraft-vanilla, paper-1-20, etc.
            $table->string('category'); // minecraft, source-games, survival, etc.
            $table->string('game'); // minecraft, csgo, rust, etc.
            $table->string('version')->nullable(); // 1.20.1, latest, etc.
            $table->text('description')->nullable();
            $table->string('docker_image'); // Docker image to use
            $table->text('startup_command'); // Default startup command
            $table->json('environment_variables')->nullable(); // Default env vars
            $table->json('configuration_files')->nullable(); // Config files to generate
            $table->integer('default_port')->nullable();
            $table->integer('min_memory')->default(512); // MB
            $table->integer('max_memory')->default(8192); // MB
            $table->integer('min_cpu')->default(50); // percentage
            $table->integer('max_cpu')->default(400); // percentage
            $table->integer('min_disk')->default(1024); // MB
            $table->integer('max_disk')->default(51200); // MB
            $table->json('port_mappings')->nullable(); // Additional ports needed
            $table->json('file_structure')->nullable(); // Default file structure
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->string('icon')->nullable(); // Icon URL or path
            $table->json('install_script')->nullable(); // Installation steps
            $table->json('supported_versions')->nullable(); // Available versions
            $table->timestamps();
            
            $table->index(['category', 'game']);
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_templates');
    }
};
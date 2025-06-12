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
        Schema::create('server_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->string('name'); // user-defined backup name
            $table->string('uuid')->unique(); // unique identifier
            $table->string('status')->default('pending'); // pending, creating, completed, failed, restoring
            $table->string('type')->default('manual'); // manual, scheduled, automatic
            $table->bigInteger('size')->default(0); // bytes
            $table->string('compression')->default('gzip'); // compression method
            $table->string('storage_location')->nullable(); // local, s3, etc.
            $table->string('file_path')->nullable(); // path to backup file
            $table->json('included_files')->nullable(); // which files/folders were backed up
            $table->json('excluded_files')->nullable(); // which files were excluded
            $table->text('error_message')->nullable(); // if backup failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('locked')->default(false); // prevent deletion
            $table->text('notes')->nullable(); // user notes
            $table->timestamps();
            
            $table->index(['server_id', 'status']);
            $table->index(['server_id', 'type']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_backups');
    }
};
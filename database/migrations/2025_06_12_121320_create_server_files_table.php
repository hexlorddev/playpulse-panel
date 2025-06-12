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
        Schema::create('server_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->string('name'); // filename
            $table->string('path'); // full path from server root
            $table->string('type'); // file, directory
            $table->bigInteger('size')->default(0); // bytes
            $table->timestamp('modified_at')->nullable();
            $table->string('permissions')->nullable(); // rwxrwxrwx
            $table->text('content_preview')->nullable(); // first few lines for text files
            $table->string('mime_type')->nullable();
            $table->boolean('is_editable')->default(false); // can be edited in web interface
            $table->boolean('is_config')->default(false); // is a configuration file
            $table->json('metadata')->nullable(); // additional file info
            $table->timestamps();
            
            $table->index(['server_id', 'path']);
            $table->index(['server_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_files');
    }
};
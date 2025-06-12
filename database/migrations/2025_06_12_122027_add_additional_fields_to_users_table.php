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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone')->nullable()->after('avatar');
            $table->string('language', 5)->default('en')->after('phone');
            $table->string('timezone')->default('UTC')->after('language');
            $table->boolean('two_factor_enabled')->default(false)->after('timezone');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->json('recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('last_activity')->nullable()->after('recovery_codes');
            $table->boolean('suspended')->default(false)->after('last_activity');
            $table->text('suspension_reason')->nullable()->after('suspended');
            
            $table->index('suspended');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'phone',
                'language',
                'timezone',
                'two_factor_enabled',
                'two_factor_secret',
                'recovery_codes',
                'last_activity',
                'suspended',
                'suspension_reason',
            ]);
        });
    }
};
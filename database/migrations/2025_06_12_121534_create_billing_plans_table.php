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
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Starter, Professional, Enterprise
            $table->string('slug')->unique(); // starter, professional, enterprise
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Monthly price
            $table->string('currency', 3)->default('USD');
            $table->string('billing_period')->default('monthly'); // monthly, yearly
            $table->integer('memory_limit')->default(1024); // MB
            $table->integer('cpu_limit')->default(100); // percentage
            $table->integer('disk_limit')->default(5120); // MB
            $table->integer('server_limit')->default(1); // number of servers
            $table->integer('database_limit')->default(1); // number of databases
            $table->integer('backup_limit')->default(3); // number of backups per server
            $table->integer('user_limit')->default(0); // sub-users (0 = unlimited)
            $table->json('features')->nullable(); // additional features
            $table->boolean('priority_support')->default(false);
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('stripe_price_id')->nullable(); // Stripe price ID
            $table->string('paypal_plan_id')->nullable(); // PayPal plan ID
            $table->timestamps();
            
            $table->index('active');
            $table->index('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
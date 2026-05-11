<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->integer('total_points_earned')->default(0);
            $table->integer('total_points_redeemed')->default(0);
            $table->decimal('lifetime_value', 12, 2)->default(0);
            $table->timestamps();
            
            $table->unique('customer_id');
        });

        Schema::create('reward_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_reward_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earn', 'redeem', 'expire', 'bonus']);
            $table->integer('points');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->index('customer_reward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_transactions');
        Schema::dropIfExists('customer_rewards');
    }
};
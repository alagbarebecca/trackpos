<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['store', 'warehouse', 'depot'])->default('store');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('location_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamps();

            $table->unique(['location_id', 'product_id']);
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_products');
        Schema::dropIfExists('locations');
    }
};
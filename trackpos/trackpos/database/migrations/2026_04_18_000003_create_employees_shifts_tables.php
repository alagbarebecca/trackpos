<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('designation')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->integer('break_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('employees');
    }
};
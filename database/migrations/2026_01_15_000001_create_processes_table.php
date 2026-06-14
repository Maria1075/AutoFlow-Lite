<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description');
            $table->enum('frequency', ['hourly', 'daily', 'weekly', 'monthly', 'manual']);
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->string('script_path')->nullable();
            $table->integer('executions_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
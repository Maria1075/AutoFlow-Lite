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
        Schema::create('triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // manual: POST /api/v1/workflows/{id}/run
            // webhook: POST /api/v1/webhooks/{token}  (URL pública única)
            // cron: expresión cron almacenada para ejecución futura
            $table->enum('type', ['manual', 'webhook', 'cron'])->default('manual');
            $table->string('cron_expression')->nullable();
            $table->string('webhook_token')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};

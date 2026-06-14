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
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            // Quién lo disparó: manual, webhook, process_execute
            $table->string('triggered_by')->default('manual');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->json('request_payload')->nullable();  // datos que llegaron al trigger
            $table->json('response_payload')->nullable(); // resultados de cada acción
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_executions');
    }
};

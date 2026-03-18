<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistant_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('context_type', 80)->nullable(); // protocol, pricing, consultation, general
            $table->string('prompt_key', 80)->nullable();
            $table->json('request_payload')->nullable();
            $table->text('response_text')->nullable();
            $table->json('response_json')->nullable();
            $table->string('model', 120)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistant_logs');
    }
};

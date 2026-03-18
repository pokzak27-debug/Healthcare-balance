<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 50); // chat, assistant
            $table->string('context_type', 80)->nullable(); // protocol, consultation, general, chat
            $table->unsignedBigInteger('related_id')->nullable(); // id de log ou message lié
            $table->text('prompt')->nullable();
            $table->longText('response')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['source', 'context_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};


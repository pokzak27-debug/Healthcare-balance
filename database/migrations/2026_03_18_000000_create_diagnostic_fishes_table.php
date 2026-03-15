<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_fishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // médecin
            $table->text('examen_clinique')->nullable();
            $table->text('examen_subjectif')->nullable();
            $table->text('examen_dynamique')->nullable();
            $table->text('examen_statique')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('protocole')->nullable();
            $table->date('date_examen')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_fishes');
    }
};

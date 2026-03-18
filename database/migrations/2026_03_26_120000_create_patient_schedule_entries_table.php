<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_schedule_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('time', 5); // HH:MM
            $table->string('pole_label', 191)->nullable(); // texte simple pour le patient : "Kiné", "Ostéo"…
            $table->foreignId('source_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_schedule_entries');
    }
};


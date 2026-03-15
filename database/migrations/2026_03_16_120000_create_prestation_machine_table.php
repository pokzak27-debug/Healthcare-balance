<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestation_machine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['prestation_id', 'machine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestation_machine');
    }
};

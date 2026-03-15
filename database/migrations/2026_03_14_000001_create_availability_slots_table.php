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
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('pole_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=lundi, 7=dimanche
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('slot_minutes')->default(30); // durée créneau (15, 30, 45, 60)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
            $table->index(['pole_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};

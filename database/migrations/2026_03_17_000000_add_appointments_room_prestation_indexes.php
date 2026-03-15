<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['appointment_date', 'room_id']);
            $table->index(['appointment_date', 'prestation_id']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['appointment_date', 'room_id']);
            $table->dropIndex(['appointment_date', 'prestation_id']);
        });
    }
};

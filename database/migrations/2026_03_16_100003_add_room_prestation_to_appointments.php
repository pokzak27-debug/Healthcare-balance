<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('pole_id')->constrained()->nullOnDelete();
            $table->foreignId('prestation_id')->nullable()->after('room_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
            $table->dropConstrainedForeignId('prestation_id');
        });
    }
};

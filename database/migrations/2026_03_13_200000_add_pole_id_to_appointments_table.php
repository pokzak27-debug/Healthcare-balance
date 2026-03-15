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
        if (Schema::hasColumn('appointments', 'pole_id')) {
            return;
        }
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('pole_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
            $table->index('pole_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['pole_id']);
        });
    }
};

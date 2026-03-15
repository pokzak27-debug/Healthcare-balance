<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('current_patient_id')->nullable()->after('current_appointment_id')
                ->constrained('patients')->nullOnDelete();
            $table->foreignId('current_prestation_id')->nullable()->after('current_patient_id')
                ->constrained('prestations')->nullOnDelete();
            $table->dateTime('occupied_started_at')->nullable()->after('current_prestation_id');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_patient_id');
            $table->dropConstrainedForeignId('current_prestation_id');
            $table->dropColumn('occupied_started_at');
        });
    }
};

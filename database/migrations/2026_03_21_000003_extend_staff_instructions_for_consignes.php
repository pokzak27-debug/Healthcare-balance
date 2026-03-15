<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_instructions', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('to_user_id')->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->after('appointment_id')->constrained()->nullOnDelete();
            $table->foreignId('prestation_id')->nullable()->after('room_id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('prestation_id');
            $table->boolean('voir_patient')->default(false)->after('duration_minutes');
            $table->boolean('is_read')->default(false)->after('voir_patient');
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->foreignId('read_by')->nullable()->after('read_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('staff_instructions', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['room_id']);
            $table->dropForeign(['prestation_id']);
            $table->dropForeign(['read_by']);
            $table->dropColumn([
                'patient_id', 'appointment_id', 'room_id', 'prestation_id',
                'duration_minutes', 'voir_patient', 'is_read', 'read_at', 'read_by'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_instructions', function (Blueprint $table) {
            $table->json('prestation_ids')->nullable()->after('prestation_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff_instructions', function (Blueprint $table) {
            $table->dropColumn('prestation_ids');
        });
    }
};

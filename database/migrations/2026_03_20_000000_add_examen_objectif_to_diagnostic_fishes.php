<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnostic_fishes', function (Blueprint $table) {
            $table->text('examen_objectif')->nullable()->after('examen_subjectif');
        });
    }

    public function down(): void
    {
        Schema::table('diagnostic_fishes', function (Blueprint $table) {
            $table->dropColumn('examen_objectif');
        });
    }
};

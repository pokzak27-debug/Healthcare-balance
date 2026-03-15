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
        Schema::table('patients', function (Blueprint $table) {
            $table->json('custom_data')->nullable()->after('notes');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->json('custom_data')->nullable();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('custom_data')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('patients', fn (Blueprint $t) => $t->dropColumn('custom_data'));
        Schema::table('appointments', fn (Blueprint $t) => $t->dropColumn('custom_data'));
        Schema::table('invoices', fn (Blueprint $t) => $t->dropColumn('custom_data'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('appointments', 'user_id')) {
            return;
        }
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('pole_id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};

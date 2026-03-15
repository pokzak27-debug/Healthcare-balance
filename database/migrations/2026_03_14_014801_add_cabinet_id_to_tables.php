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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('cabinet_id'));
        Schema::table('patients', fn (Blueprint $t) => $t->dropConstrainedForeignId('cabinet_id'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_assistant_logs')) {
            return;
        }

        Schema::table('ai_assistant_logs', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_assistant_logs')) {
            return;
        }

        Schema::table('ai_assistant_logs', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};

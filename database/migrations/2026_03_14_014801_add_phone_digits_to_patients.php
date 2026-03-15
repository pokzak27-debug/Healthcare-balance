<?php

use App\Models\Patient;
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
            $table->string('phone_digits', 20)->nullable()->after('phone');
        });

        Patient::whereNull('phone_digits')->each(function (Patient $p) {
            if ($p->phone) {
                $p->phone_digits = preg_replace('/\D/', '', $p->phone);
                $p->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('phone_digits');
        });
    }
};

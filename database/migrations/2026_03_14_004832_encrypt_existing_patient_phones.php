<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $patients = DB::table('patients')->whereNotNull('phone')->where('phone', '!=', '')->get();
        foreach ($patients as $p) {
            try {
                Crypt::decryptString($p->phone);
            } catch (\Throwable) {
                DB::table('patients')->where('id', $p->id)->update([
                    'phone' => Crypt::encryptString($p->phone),
                ]);
            }
        }
    }

    public function down(): void
    {
        $patients = DB::table('patients')->whereNotNull('phone')->get();
        foreach ($patients as $p) {
            try {
                $decrypted = Crypt::decryptString($p->phone);
                DB::table('patients')->where('id', $p->id)->update(['phone' => $decrypted]);
            } catch (\Throwable) {
                // Leave as-is if not our encryption
            }
        }
    }

};

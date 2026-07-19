<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visitor_registrations', function (Blueprint $table) {
            $table->text('encrypted_qr_token')->nullable()->after('qr_token_hash');
        });

        DB::table('visitor_registrations')
            ->whereNull('encrypted_qr_token')
            ->whereIn('status', ['active', 'checked_in'])
            ->orderBy('id')
            ->each(function ($visitor) {
                $rawToken = Str::random(64);
                DB::table('visitor_registrations')
                    ->where('id', $visitor->id)
                    ->update([
                        'encrypted_qr_token' => Crypt::encryptString($rawToken),
                        'qr_token_hash' => hash('sha256', $rawToken),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_registrations', function (Blueprint $table) {
            $table->dropColumn('encrypted_qr_token');
        });
    }
};

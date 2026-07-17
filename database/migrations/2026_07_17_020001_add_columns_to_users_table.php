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
            $table->char('uuid', 36)->unique()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default('pending')->after('password');
            $table->string('resident_type')->nullable()->after('status');
            $table->string('avatar')->nullable()->after('resident_type');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'phone',
                'status',
                'resident_type',
                'avatar',
                'last_login_at',
            ]);
        });
    }
};

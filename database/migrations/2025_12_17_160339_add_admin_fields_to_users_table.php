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
            $table->boolean('is_admin')->default(false)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('is_admin');
            $table->timestamp('last_activity_at')->nullable()->after('last_login_at');
            $table->index('is_admin');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_admin']);
            $table->dropIndex(['last_activity_at']);
            $table->dropColumn(['is_admin', 'last_login_at', 'last_activity_at']);
        });
    }
};

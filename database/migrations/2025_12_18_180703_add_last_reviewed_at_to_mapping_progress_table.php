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
        Schema::table('mapping_progress', function (Blueprint $table) {
            $table->json('last_reviewed_at')->nullable()->after('completed_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapping_progress', function (Blueprint $table) {
            $table->dropColumn('last_reviewed_at');
        });
    }
};

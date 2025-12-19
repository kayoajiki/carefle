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
        Schema::table('strengths_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('strengths_reports', 'diagnosis_report')) {
                $table->json('diagnosis_report')->nullable()->after('content');
            }
            if (!Schema::hasColumn('strengths_reports', 'diary_report')) {
                $table->json('diary_report')->nullable()->after('diagnosis_report');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strengths_reports', function (Blueprint $table) {
            $table->dropColumn(['diagnosis_report', 'diary_report']);
        });
    }
};

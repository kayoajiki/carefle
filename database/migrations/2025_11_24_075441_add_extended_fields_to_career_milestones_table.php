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
        Schema::table('career_milestones', function (Blueprint $table) {
            $table->foreignId('wcm_sheet_id')
                ->nullable()
                ->after('user_id')
                ->constrained('wcm_sheets')
                ->nullOnDelete();

            $table->string('will_theme', 255)->nullable()->after('title');
            $table->json('mandala_data')->nullable()->after('description');
            $table->text('action_overview')->nullable()->after('mandala_data');

            $table->date('target_date')->nullable()->after('target_year');
            $table->unsignedTinyInteger('impact_score')->default(0)->after('status');
            $table->unsignedTinyInteger('effort_score')->default(0)->after('impact_score');
            $table->unsignedInteger('progress_points')->default(0)->after('achievement_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('career_milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wcm_sheet_id');
            $table->dropColumn([
                'will_theme',
                'mandala_data',
                'action_overview',
                'target_date',
                'impact_score',
                'effort_score',
                'progress_points',
            ]);
        });
    }
};

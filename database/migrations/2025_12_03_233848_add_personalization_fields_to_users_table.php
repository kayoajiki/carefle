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
            $table->enum('reflection_style', ['structured', 'freeform', 'guided'])->nullable()->after('profile_completed')->comment('内省スタイル: structured=構造化, freeform=自由形式, guided=ガイド付き');
            $table->enum('goal_setting_style', ['detailed', 'broad', 'flexible'])->nullable()->after('reflection_style')->comment('目標設定スタイル: detailed=詳細, broad=広範, flexible=柔軟');
            $table->json('ai_companion_preferences')->nullable()->after('goal_setting_style')->comment('AI伴走の好み設定');
            $table->time('preferred_reflection_time')->nullable()->after('ai_companion_preferences')->comment('好みの内省時間');
            $table->boolean('enable_adaptive_reminders')->default(true)->after('preferred_reflection_time')->comment('適応的リマインダーを有効化');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'reflection_style',
                'goal_setting_style',
                'ai_companion_preferences',
                'preferred_reflection_time',
                'enable_adaptive_reminders',
            ]);
        });
    }
};
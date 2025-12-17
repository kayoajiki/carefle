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
        Schema::create('diary_goal_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diary_id')->constrained('diaries')->onDelete('cascade');
            $table->enum('connection_type', ['milestone', 'wcm_will'])->comment('接続タイプ: milestone=マイルストーン, wcm_will=WCMシートのWillテーマ');
            $table->unsignedBigInteger('connected_id')->comment('接続先ID（career_milestones.id または wcm_sheets.id）');
            $table->unsignedTinyInteger('connection_score')->default(0)->comment('接続度（0-100）');
            $table->text('connection_reason')->nullable()->comment('関連している理由（AIが生成）');
            $table->string('will_theme', 255)->nullable()->comment('関連するWillテーマ');
            $table->timestamps();

            $table->index(['diary_id', 'connection_type']);
            $table->index(['connection_type', 'connected_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diary_goal_connections');
    }
};
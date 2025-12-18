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
        Schema::create('strengths_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('content'); // 持ち味レポのコンテンツ（title, agenda, strengths）
            $table->json('diagnosis_report')->nullable(); // 診断レポート
            $table->json('diary_report')->nullable(); // 日記レポート
            $table->timestamp('generated_at'); // 生成日時
            $table->timestamps();
            
            $table->index(['user_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strengths_reports');
    }
};

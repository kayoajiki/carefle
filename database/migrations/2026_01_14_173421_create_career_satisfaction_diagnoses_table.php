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
        Schema::create('career_satisfaction_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('work_score')->nullable(); // 0-100
            $table->json('work_pillar_scores')->nullable(); // pillar別のスコア
            $table->string('state_type', 1)->nullable(); // A, B, or C
            $table->boolean('is_completed')->default(false); // 完了フラグ
            $table->boolean('is_draft')->default(true); // 下書きフラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_satisfaction_diagnoses');
    }
};

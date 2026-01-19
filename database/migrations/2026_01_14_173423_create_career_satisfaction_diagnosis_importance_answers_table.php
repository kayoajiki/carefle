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
        Schema::create('career_satisfaction_diagnosis_importance_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_satisfaction_diagnosis_id')->constrained('career_satisfaction_diagnoses')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->unsignedTinyInteger('importance_value'); // 1-5
            $table->timestamps();
            $table->unique(['career_satisfaction_diagnosis_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_satisfaction_diagnosis_importance_answers');
    }
};

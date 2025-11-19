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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('question_id')->unique(); // work_purpose, life_familyなど
            $table->enum('type', ['work', 'life']);
            $table->string('pillar'); // purpose, profession, people, etc.
            $table->integer('weight')->nullable(); // Work用の重み（合計100）
            $table->text('text'); // 質問文
            $table->text('helper')->nullable(); // 補足説明
            $table->json('options'); // 選択肢のJSON
            $table->integer('order')->default(0); // 表示順
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

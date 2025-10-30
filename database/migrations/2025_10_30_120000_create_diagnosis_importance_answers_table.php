<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diagnosis_importance_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnosis_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->unsignedTinyInteger('importance_value'); // 1-5
            $table->timestamps();
            $table->unique(['diagnosis_id','question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis_importance_answers');
    }
};



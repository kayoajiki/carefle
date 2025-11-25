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
        Schema::create('values_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('value_name', 255);
            $table->enum('source_type', ['life_event', 'diagnosis', 'diary', 'personality_assessment', 'manual'])->default('manual');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('confidence_score')->default(50)->comment('0-100');
            $table->date('first_detected_at');
            $table->date('last_detected_at');
            $table->timestamps();
            
            $table->index(['user_id', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('values_extractions');
    }
};

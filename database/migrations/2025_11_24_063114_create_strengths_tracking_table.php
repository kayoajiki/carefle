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
        Schema::create('strengths_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('strength_name', 255);
            $table->foreignId('assessment_id')->nullable()->constrained('personality_assessments')->onDelete('set null');
            $table->enum('detected_from', ['assessment', 'life_event', 'diagnosis', 'manual'])->default('manual');
            $table->integer('strength_level')->default(5)->comment('1-10');
            $table->date('detected_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'detected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strengths_tracking');
    }
};

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
        Schema::create('career_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('target_year');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('category', ['career', 'skill', 'life', 'other'])->default('career');
            $table->enum('status', ['planned', 'in_progress', 'achieved', 'cancelled'])->default('planned');
            $table->integer('achievement_rate')->default(0)->comment('0-100');
            $table->foreignId('linked_life_event_id')->nullable()->constrained('life_events')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['user_id', 'target_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_milestones');
    }
};

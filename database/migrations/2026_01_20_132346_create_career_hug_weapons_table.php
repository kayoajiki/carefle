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
        Schema::create('career_hug_weapons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_hug_id');
            $table->enum('weapon', ['career_satisfaction_diagnosis', 'wcm', 'life_history', 'judgment_organization_frame']);
            $table->timestamps();
            
            $table->foreign('career_hug_id', 'ch_w_ch_id_foreign')
                ->references('id')
                ->on('career_hugs')
                ->onDelete('cascade');
            
            $table->unique(['career_hug_id', 'weapon'], 'ch_w_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_hug_weapons');
    }
};

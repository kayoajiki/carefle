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
        Schema::create('career_hug_level_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_hug_id');
            $table->enum('level', ['level1', 'level2', 'level3']);
            $table->date('date');
            $table->timestamps();
            
            $table->foreign('career_hug_id', 'ch_ld_ch_id_foreign')
                ->references('id')
                ->on('career_hugs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_hug_level_dates');
    }
};

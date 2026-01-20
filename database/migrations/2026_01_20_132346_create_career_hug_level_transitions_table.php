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
        Schema::create('career_hug_level_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_hug_id');
            $table->enum('from_level', ['level0', 'level1', 'level2', 'level3', 'cf_continuation']);
            $table->enum('to_level', ['level0', 'level1', 'level2', 'level3', 'cf_continuation', 'graduation']);
            $table->enum('transition_reason', ['self_sufficient', 'judgment_organization_completed', 'continuation_needed', 'timing_off']);
            $table->string('reason_note')->nullable();
            $table->timestamps();
            
            $table->foreign('career_hug_id', 'ch_lt_ch_id_foreign')
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
        Schema::dropIfExists('career_hug_level_transitions');
    }
};

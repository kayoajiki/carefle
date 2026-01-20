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
        Schema::create('career_hug_contact_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_hug_id');
            $table->date('contact_date');
            $table->enum('contact_type', ['session', 'chat', 'follow_up']);
            $table->text('theme')->nullable();
            $table->text('decided_matters')->nullable();
            $table->text('next_action')->nullable();
            $table->timestamps();
            
            $table->foreign('career_hug_id', 'ch_cl_ch_id_foreign')
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
        Schema::dropIfExists('career_hug_contact_logs');
    }
};

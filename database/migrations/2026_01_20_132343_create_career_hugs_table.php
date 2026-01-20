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
        Schema::create('career_hugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('usage_type', ['paid', 'free'])->nullable();
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('current_level', ['level1', 'level2', 'level3'])->nullable();
            $table->enum('main_purpose', ['judgment_organization', 'action_design', 'continuation_adjustment'])->nullable();
            $table->string('entry_trigger')->nullable();
            $table->enum('session_density', ['low', 'medium', 'high'])->nullable();
            $table->enum('current_phase', ['state_understanding', 'verbalization', 'judgment_organization', 'action', 'continuation_adjustment'])->nullable();
            $table->enum('status', ['not_started', 'in_use', 'paused', 'completed'])->default('not_started');
            $table->date('last_session_date')->nullable();
            $table->date('next_session_date')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->nullable();
            $table->text('contract_rules')->nullable();
            $table->json('ng_actions')->nullable();
            $table->text('handover_memo')->nullable();
            $table->string('admin_summary')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id', 'ch_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('assigned_admin_id', 'ch_assigned_admin_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_hugs');
    }
};

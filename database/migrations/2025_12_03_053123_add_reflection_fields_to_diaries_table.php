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
        Schema::table('diaries', function (Blueprint $table) {
            $table->enum('reflection_type', ['daily', 'yesterday', 'weekly', 'deep', 'moya_moya'])->nullable()->after('content');
            $table->foreignId('linked_milestone_id')->nullable()->after('reflection_type')->constrained('career_milestones')->onDelete('set null');
            $table->foreignId('chat_conversation_id')->nullable()->after('linked_milestone_id')->constrained('reflection_chat_conversations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diaries', function (Blueprint $table) {
            $table->dropForeign(['linked_milestone_id']);
            $table->dropForeign(['chat_conversation_id']);
            $table->dropColumn(['reflection_type', 'linked_milestone_id', 'chat_conversation_id']);
        });
    }
};
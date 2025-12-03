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
        Schema::table('reflection_chat_conversations', function (Blueprint $table) {
            $table->foreignId('diary_id')->nullable()->after('user_id')->constrained('diaries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reflection_chat_conversations', function (Blueprint $table) {
            $table->dropForeign(['diary_id']);
            $table->dropColumn('diary_id');
        });
    }
};

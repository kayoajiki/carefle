<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('goal_image_url')->nullable()->after('goal_image');
            $table->enum('goal_display_mode', ['text', 'image'])->default('text')->after('goal_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['goal_image_url', 'goal_display_mode']);
        });
    }
};


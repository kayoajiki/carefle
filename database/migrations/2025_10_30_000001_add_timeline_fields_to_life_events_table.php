<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('life_events', function (Blueprint $table) {
            $table->string('timeline_color', 7)->nullable()->after('motivation'); // e.g. #FFE4E6
            $table->string('timeline_label', 32)->nullable()->after('timeline_color'); // e.g. 幼少期
        });
    }

    public function down(): void
    {
        Schema::table('life_events', function (Blueprint $table) {
            $table->dropColumn(['timeline_color', 'timeline_label']);
        });
    }
};



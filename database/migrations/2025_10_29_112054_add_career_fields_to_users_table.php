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
        Schema::table('users', function (Blueprint $table) {
            $table->string('prefecture')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('prefecture');
            $table->string('industry')->nullable()->after('occupation');
            $table->integer('work_experience_years')->nullable()->after('industry');
            $table->string('education')->nullable()->after('work_experience_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prefecture', 'occupation', 'industry', 'work_experience_years', 'education']);
        });
    }
};

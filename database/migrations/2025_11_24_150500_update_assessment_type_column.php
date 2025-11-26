<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('personality_assessments_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('assessment_type', ['mbti', 'strengthsfinder', 'enneagram', 'big5', 'ffs', 'custom'])->default('custom');
            $table->string('assessment_name', 255)->nullable();
            $table->json('result_data')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'assessment_type']);
        });

        $records = DB::table('personality_assessments')->orderBy('id')->get();
        foreach ($records as $record) {
            DB::table('personality_assessments_new')->insert((array) $record);
        }

        Schema::drop('personality_assessments');
        Schema::rename('personality_assessments_new', 'personality_assessments');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('personality_assessments_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('assessment_type', ['mbti', 'strengthsfinder', 'enneagram', 'big5', 'custom'])->default('custom');
            $table->string('assessment_name', 255)->nullable();
            $table->json('result_data')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'assessment_type']);
        });

        $records = DB::table('personality_assessments')->orderBy('id')->get();
        foreach ($records as $record) {
            $type = $record->assessment_type === 'ffs' ? 'custom' : $record->assessment_type;
            $record->assessment_type = $type;
            DB::table('personality_assessments_old')->insert((array) $record);
        }

        Schema::drop('personality_assessments');
        Schema::rename('personality_assessments_old', 'personality_assessments');

        Schema::enableForeignKeyConstraints();
    }
};



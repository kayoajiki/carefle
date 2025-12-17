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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_filename', 255);
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size'); // bytes
            $table->timestamp('uploaded_at')->nullable();
            $table->string('memo', 20)->nullable();
            $table->timestamps();
            
            // インデックス
            $table->index('user_id');
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
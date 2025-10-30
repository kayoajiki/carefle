<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wcm_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 100)->nullable();
            $table->text('will_text')->nullable();
            $table->text('can_text')->nullable();
            $table->text('must_text')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wcm_sheets');
    }
};



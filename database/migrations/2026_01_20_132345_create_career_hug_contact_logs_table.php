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
        if (!Schema::hasTable('career_hug_contact_logs')) {
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
        } else {
            // テーブルが既に存在する場合、外部キー制約が正しく設定されているか確認
            if (!$this->hasForeignKey('career_hug_contact_logs', 'ch_cl_ch_id_foreign')) {
                $existingForeignKey = $this->getExistingForeignKey('career_hug_contact_logs', 'career_hug_id');
                if ($existingForeignKey) {
                    Schema::table('career_hug_contact_logs', function (Blueprint $table) use ($existingForeignKey) {
                        $table->dropForeign($existingForeignKey);
                    });
                }
                
                Schema::table('career_hug_contact_logs', function (Blueprint $table) {
                    $table->foreign('career_hug_id', 'ch_cl_ch_id_foreign')
                        ->references('id')
                        ->on('career_hugs')
                        ->onDelete('cascade');
                });
            }
        }
    }
    
    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?",
            [$database, $table, $foreignKey]
        );
        
        return count($result) > 0;
    }
    
    private function getExistingForeignKey(string $table, string $column): ?string
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND COLUMN_NAME = ? 
             AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$database, $table, $column]
        );
        
        return count($result) > 0 ? $result[0]->CONSTRAINT_NAME : null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_hug_contact_logs');
    }
};

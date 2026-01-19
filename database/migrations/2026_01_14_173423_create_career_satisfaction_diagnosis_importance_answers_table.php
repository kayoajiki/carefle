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
        if (!Schema::hasTable('career_satisfaction_diagnosis_importance_answers')) {
            Schema::create('career_satisfaction_diagnosis_importance_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('career_satisfaction_diagnosis_id');
                $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
                $table->unsignedTinyInteger('importance_value'); // 1-5
                $table->timestamps();
                
                $table->foreign('career_satisfaction_diagnosis_id', 'cs_diag_imp_ans_diag_id_foreign')
                    ->references('id')
                    ->on('career_satisfaction_diagnoses')
                    ->onDelete('cascade');
                
                $table->unique(['career_satisfaction_diagnosis_id', 'question_id']);
            });
        } else {
            // テーブルが既に存在する場合、外部キー制約が正しく設定されているか確認
            if (!$this->hasForeignKey('career_satisfaction_diagnosis_importance_answers', 'cs_diag_imp_ans_diag_id_foreign')) {
                // 既存の外部キー制約を削除（存在する場合）
                $existingForeignKey = $this->getExistingForeignKey('career_satisfaction_diagnosis_importance_answers', 'career_satisfaction_diagnosis_id');
                if ($existingForeignKey) {
                    Schema::table('career_satisfaction_diagnosis_importance_answers', function (Blueprint $table) use ($existingForeignKey) {
                        $table->dropForeign($existingForeignKey);
                    });
                }
                
                Schema::table('career_satisfaction_diagnosis_importance_answers', function (Blueprint $table) {
                    // 短い名前で外部キー制約を追加
                    $table->foreign('career_satisfaction_diagnosis_id', 'cs_diag_imp_ans_diag_id_foreign')
                        ->references('id')
                        ->on('career_satisfaction_diagnoses')
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
        Schema::dropIfExists('career_satisfaction_diagnosis_importance_answers');
    }
};

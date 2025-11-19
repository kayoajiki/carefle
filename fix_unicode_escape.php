<?php
/**
 * Unicodeエスケープを修正するスクリプト
 * 
 * 使用方法：
 * 1. このファイルをEC2サーバーにアップロード
 * 2. EC2上で実行: php fix_unicode_escape.php
 * 
 * または、Tinker内で直接実行する場合は、
 * 以下のコードをコピー&ペーストしてください。
 */

use App\Models\Question;

echo "Unicodeエスケープの修正を開始します...\n\n";

$count = 0;
$fixed = 0;

Question::cursor()->each(function ($question) use (&$count, &$fixed) {
    $count++;
    $options = $question->options;
    
    // optionsが配列の場合は一旦JSONに変換
    if (is_array($options)) {
        $optionsJson = json_encode($options);
    } else {
        $optionsJson = $options;
    }
    
    // JSON文字列かチェック
    if (is_string($optionsJson)) {
        // Unicodeエスケープが含まれているかチェック
        if (strpos($optionsJson, '\\u') !== false) {
            $decoded = json_decode($optionsJson, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // JSON_UNESCAPED_UNICODEフラグで再エンコード
                $question->options = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                $question->save();
                $fixed++;
                
                echo "✓ ID {$question->id} ({$question->question_id}) を修正しました\n";
            } else {
                echo "✗ ID {$question->id} ({$question->question_id}) のJSON解析に失敗しました\n";
            }
        }
    }
});

echo "\n修正完了: {$fixed}件のレコードを修正しました（全{$count}件中）\n";


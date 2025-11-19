<?php
/**
 * データベースのquestionsテーブルのoptionsカラム内の
 * uXXXX形式（バックスラッシュなし）を\uXXXX形式（バックスラッシュあり）に修正
 * 
 * 使用方法：
 * EC2上で実行: php artisan tinker
 * その後、以下のコードをコピー&ペースト
 */

use App\Models\Question;

echo "Unicodeエスケープの修正を開始します...\n\n";

$count = 0;
$fixed = 0;

Question::cursor()->each(function ($question) use (&$count, &$fixed) {
    $count++;
    
    // 生のデータベース値を取得（アクセサを通さない）
    $rawOptions = $question->getRawOriginal('options');
    
    if (is_string($rawOptions)) {
        // uXXXX形式（バックスラッシュなし）が含まれているかチェック
        if (preg_match('/u[0-9a-fA-F]{4}/', $rawOptions)) {
            // JSON文字列内の文字列値の中の uXXXX を \uXXXX に変換
            $fixedJson = preg_replace_callback('/"([^"]*)"/', function ($matches) {
                $content = $matches[1];
                // uXXXX形式を \uXXXX 形式に変換（既に \uXXXX の場合はスキップ）
                $fixed = preg_replace_callback('/(?<!\\\\)u([0-9a-fA-F]{4})/i', function ($m) {
                    return '\\u' . strtolower($m[1]);
                }, $content);
                return '"' . $fixed . '"';
            }, $rawOptions);
            
            // 修正後のJSONが正しいか確認
            $testDecode = json_decode($fixedJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($testDecode)) {
                // データベースを更新
                $question->setRawAttributes(['options' => $fixedJson], false);
                $question->save(['timestamps' => false]); // タイムスタンプを更新しない
                $fixed++;
                
                echo "✓ ID {$question->id} ({$question->question_id}) を修正しました\n";
            } else {
                echo "✗ ID {$question->id} ({$question->question_id}) のJSON修正に失敗しました\n";
            }
        }
    }
});

echo "\n修正完了: {$fixed}件のレコードを修正しました（全{$count}件中）\n";


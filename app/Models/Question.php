<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'question_id',
        'type',
        'pillar',
        'weight',
        'text',
        'helper',
        'options',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'weight' => 'integer',
    ];

    /**
     * Get the options attribute with Unicode escape handling
     * 
     * データベースから取得したJSON文字列（Unicodeエスケープ含む）を
     * 配列に変換し、Unicodeエスケープを解除します
     */
    public function getOptionsAttribute($value)
    {
        // データベースから取得した値をJSONデコード
        if (is_string($value)) {
            // まず、JSON文字列内の uXXXX 形式（バックスラッシュなし）を \uXXXX 形式に修正
            $fixedJson = preg_replace_callback('/"([^"]*)"/', function ($matches) {
                $content = $matches[1];
                // uXXXX形式（4桁の16進数）を \uXXXX 形式に変換
                // ただし、既に \uXXXX 形式の場合はスキップ
                $fixed = preg_replace_callback('/(?<!\\\\)u([0-9a-fA-F]{4})/', function ($m) {
                    return '\\u' . strtolower($m[1]);
                }, $content);
                return '"' . $fixed . '"';
            }, $value);
            
            $decoded = json_decode($fixedJson, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            
            // 修正後のJSONでもデコードできない場合は、元のJSONを試す
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // 配列内の文字列を再帰的に処理
                return $this->decodeUnicodeRecursive($decoded);
            }
        }
        
        // すでに配列の場合はそのまま返す
        if (is_array($value)) {
            return $this->decodeUnicodeRecursive($value);
        }
        
        return [];
    }

    /**
     * 配列を再帰的に処理して、Unicodeエスケープ（uXXXX形式）を通常の文字に変換
     */
    protected function decodeUnicodeRecursive($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'decodeUnicodeRecursive'], $data);
        }
        
        if (is_string($data)) {
            // uXXXX形式（バックスラッシュなしのUnicodeエスケープ）を検出
            // 例: "u3068u3066u3082" → "とても"
            if (preg_match('/u[0-9a-fA-F]{4}/i', $data)) {
                // uXXXX形式を直接Unicodeコードポイントとして解釈してUTF-8文字に変換
                $result = preg_replace_callback('/u([0-9a-fA-F]{4})/i', function ($matches) {
                    $codepoint = intval($matches[1], 16);
                    // UnicodeコードポイントをUTF-8バイト列に変換
                    if ($codepoint <= 0x7F) {
                        // 1バイト文字
                        return chr($codepoint);
                    } elseif ($codepoint <= 0x7FF) {
                        // 2バイト文字
                        return chr(0xC0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3F));
                    } elseif ($codepoint <= 0xFFFF) {
                        // 3バイト文字（日本語文字はここ）
                        return chr(0xE0 | ($codepoint >> 12)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
                    } elseif ($codepoint <= 0x10FFFF) {
                        // 4バイト文字
                        return chr(0xF0 | ($codepoint >> 18)) . chr(0x80 | (($codepoint >> 12) & 0x3F)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
                    }
                    return $matches[0]; // 変換できない場合は元の値を返す
                }, $data);
                
                return $result;
            }
        }
        
        return $data;
    }

    /**
     * Set the options attribute with Unicode preservation
     * 
     * 配列をJSON文字列に変換する際、Unicodeエスケープをしないように設定します
     */
    public function setOptionsAttribute($value)
    {
        if (is_array($value)) {
            // JSON_UNESCAPED_UNICODEフラグでUnicodeエスケープなしで保存
            $this->attributes['options'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['options'] = $value;
        }
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DiagnosisAnswer::class);
    }
}

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
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        
        // すでに配列の場合はそのまま返す
        if (is_array($value)) {
            return $value;
        }
        
        return [];
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

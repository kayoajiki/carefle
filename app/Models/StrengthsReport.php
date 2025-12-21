<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrengthsReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'diagnosis_report',
        'diary_report',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'diagnosis_report' => 'array',
            'diary_report' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the strengths report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the latest strengths report for a user.
     */
    public static function getLatestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->latest('generated_at')
            ->first();
    }

    /**
     * Check if user can update the report (1 month restriction).
     * 開発環境（local）では制限を緩和（1日経過で更新可能）
     */
    public static function canUpdate(int $userId): bool
    {
        $latest = static::getLatestForUser($userId);
        
        if (!$latest) {
            return true; // 初回生成は可能
        }
        
        // 開発環境（local）では1日経過で更新可能、本番環境では1ヶ月
        $isLocal = app()->environment('local');
        $requiredInterval = $isLocal ? now()->subDay() : now()->subMonth();
        
        return $latest->generated_at->isBefore($requiredInterval);
    }
}

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
     */
    public static function canUpdate(int $userId): bool
    {
        $latest = static::getLatestForUser($userId);
        
        if (!$latest) {
            return true; // 初回生成は可能
        }
        
        // 最後の更新から1ヶ月経過しているかチェック
        $oneMonthAgo = now()->subMonth();
        return $latest->generated_at->isBefore($oneMonthAgo);
    }
}

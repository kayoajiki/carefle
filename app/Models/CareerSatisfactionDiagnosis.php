<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerSatisfactionDiagnosis extends Model
{
    protected $fillable = [
        'user_id',
        'work_score',
        'work_pillar_scores',
        'state_type',
        'is_completed',
        'is_draft',
        'is_admin_visible',
    ];

    protected $casts = [
        'work_pillar_scores' => 'array',
        'is_completed' => 'boolean',
        'is_draft' => 'boolean',
        'is_admin_visible' => 'boolean',
        'work_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CareerSatisfactionDiagnosisAnswer::class, 'career_satisfaction_diagnosis_id');
    }

    public function importanceAnswers(): HasMany
    {
        return $this->hasMany(CareerSatisfactionDiagnosisImportanceAnswer::class, 'career_satisfaction_diagnosis_id');
    }

    /**
     * 状態タイプを判定する
     * 
     * @param array $workPillarScores 満足度pillarスコア
     * @param array $importanceWork 重要度pillarスコア
     * @param int $workScore 全体のwork_score
     * @return string 'A', 'B', or 'C'
     */
    public static function determineStateType(array $workPillarScores, array $importanceWork, int $workScore): string
    {
        // 引っかかりポイントをカウント（満足度 < 重要度）
        $stuckPoints = [];
        $maxDiff = null;
        
        foreach ($workPillarScores as $pillar => $satisfactionScore) {
            $importanceScore = $importanceWork[$pillar] ?? null;
            if ($importanceScore !== null && $satisfactionScore !== null) {
                $diff = $satisfactionScore - $importanceScore;
                if ($diff < 0) {
                    $stuckPoints[] = $pillar;
                    if ($maxDiff === null || $diff < $maxDiff) {
                        $maxDiff = $diff;
                    }
                }
            }
        }
        
        $stuckPointCount = count($stuckPoints);
        
        // 状態タイプC（25%）: 今は動かない判断が妥当
        if ($stuckPointCount === 0) {
            return 'C';
        }
        
        if ($stuckPointCount >= 1 && $stuckPointCount <= 2) {
            if ($maxDiff >= -10 && $workScore >= 70) {
                return 'C';
            }
        }
        
        // 状態タイプA（25%）: 一人で内省を続けられる
        if ($stuckPointCount >= 1 && $stuckPointCount <= 2) {
            if ($maxDiff >= -10 && $workScore < 70) {
                return 'A';
            }
        }
        
        if ($stuckPointCount >= 3) {
            if ($maxDiff >= -10 && $workScore >= 70) {
                return 'A';
            }
        }
        
        // 状態タイプB（50%）: 一人だと堂々巡りになりやすい
        // 上記の条件に当てはまらないすべてのケース
        return 'B';
    }
}


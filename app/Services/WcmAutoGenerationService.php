<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\Diagnosis;
use App\Models\DiagnosisAnswer;
use App\Models\PersonalityAssessment;
use App\Models\WcmSheet;
use App\Models\CareerMilestone;
use App\Models\LifeEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WcmAutoGenerationService
{
    public function __construct(
        private BedrockService $bedrockService
    ) {
    }

    /**
     * Willを生成
     */
    public function generateWill(?int $userId = null, ?string $existingWill = null): ?string
    {
        $context = $this->collectUserContext($userId);
        $prompt = $this->buildPrompt('will', $context, $existingWill);
        
        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );
            
            if ($response === null) {
                Log::error('WcmAutoGenerationService: Failed to generate Will');
                return null;
            }
            
            return $this->formatResponse($response);
        } catch (\Exception $e) {
            Log::error('WcmAutoGenerationService: Error generating Will', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Canを生成
     */
    public function generateCan(?int $userId = null, ?string $existingCan = null): ?string
    {
        $context = $this->collectUserContext($userId);
        $prompt = $this->buildPrompt('can', $context, $existingCan);
        
        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );
            
            if ($response === null) {
                Log::error('WcmAutoGenerationService: Failed to generate Can');
                return null;
            }
            
            return $this->formatResponse($response);
        } catch (\Exception $e) {
            Log::error('WcmAutoGenerationService: Error generating Can', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mustを生成
     */
    public function generateMust(?int $userId = null, ?string $existingMust = null): ?string
    {
        $context = $this->collectUserContext($userId);
        $prompt = $this->buildPrompt('must', $context, $existingMust);
        
        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );
            
            if ($response === null) {
                Log::error('WcmAutoGenerationService: Failed to generate Must');
                return null;
            }
            
            return $this->formatResponse($response);
        } catch (\Exception $e) {
            Log::error('WcmAutoGenerationService: Error generating Must', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * ユーザーのコンテキストを収集
     */
    private function collectUserContext(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        
        $context = [
            'diaries' => [],
            'diagnoses' => [],
            'assessments' => [],
            'latest_wcm' => null,
            'milestones' => [],
            'life_events' => [],
        ];

        // 直近1ヶ月の日記を取得
        $oneMonthAgo = now()->subMonth();
        $context['diaries'] = Diary::where('user_id', $userId)
            ->where('date', '>=', $oneMonthAgo)
            ->orderByDesc('date')
            ->get();

        // すべての診断結果を取得（完了済みのみ）
        $diagnoses = Diagnosis::where('user_id', $userId)
            ->where('is_completed', true)
            ->orderByDesc('created_at')
            ->get();

        foreach ($diagnoses as $diagnosis) {
            $answers = DiagnosisAnswer::where('diagnosis_id', $diagnosis->id)
                ->with('question')
                ->get();
            
            $context['diagnoses'][] = [
                'diagnosis' => $diagnosis,
                'answers' => $answers,
            ];
        }

        // すべての自己診断結果を取得
        $context['assessments'] = PersonalityAssessment::where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->get();

        // 最新のWCMシートを取得（参考用）
        $context['latest_wcm'] = WcmSheet::where('user_id', $userId)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        // 進行中のマイルストーンを取得
        $context['milestones'] = CareerMilestone::where('user_id', $userId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('target_date')
            ->limit(5)
            ->get();

        // 最近の人生史イベントを取得
        $context['life_events'] = LifeEvent::where('user_id', $userId)
            ->orderByDesc('year')
            ->limit(5)
            ->get();

        return $context;
    }

    /**
     * プロンプトを構築
     */
    private function buildPrompt(string $type, array $context, ?string $existingContent = null): string
    {
        $prompt = "ユーザーの情報を基に、WCMシートの" . strtoupper($type) . "セクションを生成してください。\n\n";
        
        // 日記の情報
        if (count($context['diaries']) > 0) {
            $prompt .= "【直近1ヶ月の日記】\n";
            foreach ($context['diaries'] as $diary) {
                $prompt .= "- {$diary->date->format('Y年m月d日')}（モチベーション: {$diary->motivation}/100）\n";
                if ($diary->content) {
                    $content = mb_substr($diary->content, 0, 200);
                    $prompt .= "  {$content}\n";
                }
            }
            $prompt .= "\n";
        }

        // 診断結果の情報
        if (count($context['diagnoses']) > 0) {
            $prompt .= "【現職満足度診断結果】\n";
            foreach ($context['diagnoses'] as $diagnosisData) {
                $diagnosis = $diagnosisData['diagnosis'];
                $prompt .= "- Workスコア: {$diagnosis->work_score}点、Lifeスコア: {$diagnosis->life_score}点\n";
                
                if ($diagnosis->work_pillar_scores) {
                    $prompt .= "  Workピラー: " . json_encode($diagnosis->work_pillar_scores, JSON_UNESCAPED_UNICODE) . "\n";
                }
                if ($diagnosis->life_pillar_scores) {
                    $prompt .= "  Lifeピラー: " . json_encode($diagnosis->life_pillar_scores, JSON_UNESCAPED_UNICODE) . "\n";
                }
                
                // コメントを含む回答
                $comments = [];
                foreach ($diagnosisData['answers'] as $answer) {
                    if ($answer->comment) {
                        $comments[] = $answer->comment;
                    }
                }
                if (count($comments) > 0) {
                    $prompt .= "  コメント: " . implode('、', array_slice($comments, 0, 5)) . "\n";
                }
            }
            $prompt .= "\n";
        }

        // 自己診断結果の情報
        if (count($context['assessments']) > 0) {
            $prompt .= "【自己診断結果】\n";
            foreach ($context['assessments'] as $assessment) {
                $prompt .= "- タイプ: {$assessment->assessment_type}";
                if ($assessment->assessment_name) {
                    $prompt .= "（{$assessment->assessment_name}）";
                }
                $prompt .= "\n";
                
                $data = $assessment->result_data ?? [];
                if (!empty($data)) {
                    $formatted = $this->formatAssessmentData($assessment->assessment_type, $data);
                    if ($formatted) {
                        $prompt .= "  {$formatted}\n";
                    }
                }
            }
            $prompt .= "\n";
        }

        // 既存のWCMシート（参考用）
        if ($context['latest_wcm']) {
            $prompt .= "【既存のWCMシート（参考）】\n";
            if ($context['latest_wcm']->will_text) {
                $prompt .= "Will: " . mb_substr($context['latest_wcm']->will_text, 0, 200) . "\n";
            }
            if ($context['latest_wcm']->can_text) {
                $prompt .= "Can: " . mb_substr($context['latest_wcm']->can_text, 0, 200) . "\n";
            }
            if ($context['latest_wcm']->must_text) {
                $prompt .= "Must: " . mb_substr($context['latest_wcm']->must_text, 0, 200) . "\n";
            }
            $prompt .= "\n";
        }

        // マイルストーン
        if (count($context['milestones']) > 0) {
            $prompt .= "【進行中のマイルストーン】\n";
            foreach ($context['milestones'] as $milestone) {
                $prompt .= "- {$milestone->title}";
                if ($milestone->will_theme) {
                    $prompt .= "（テーマ: {$milestone->will_theme}）";
                }
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }

        // 人生史
        if (count($context['life_events']) > 0) {
            $prompt .= "【人生のターニングポイント】\n";
            foreach ($context['life_events'] as $event) {
                $prompt .= "- {$event->year}年: {$event->title}";
                if ($event->description) {
                    $prompt .= "（" . mb_substr($event->description, 0, 100) . "）";
                }
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }

        // 既存の入力内容（あれば）
        if ($existingContent && trim($existingContent) !== '') {
            $prompt .= "【既存の入力内容（参考）】\n";
            $prompt .= mb_substr($existingContent, 0, 500) . "\n\n";
        }

        // 生成指示
        $typeNames = [
            'will' => 'Will（こう在りたい）',
            'can' => 'Can（できること）',
            'must' => 'Must（やるべきこと）',
        ];
        
        $typeName = $typeNames[$type] ?? strtoupper($type);
        
        $prompt .= "【生成指示】\n";
        $prompt .= "上記の情報を基に、{$typeName}セクションの内容を5項目生成してください。\n\n";
        
        if ($type === 'will') {
            $prompt .= "Willは「こう在りたい」という理想や価値観を表現してください。\n";
            $prompt .= "ユーザーの理想の姿、目指したい状態、大切にしたい価値観を反映してください。\n";
        } elseif ($type === 'can') {
            $prompt .= "Canは「できること」という強みやスキルを表現してください。\n";
            $prompt .= "ユーザーの能力、経験、強み、実績を反映してください。\n";
        } elseif ($type === 'must') {
            $prompt .= "Mustは「やるべきこと」という責任や義務、改善点を表現してください。\n";
            $prompt .= "ユーザーが取り組むべき課題、改善すべき点、果たすべき責任を反映してください。\n";
        }
        
        $prompt .= "\n";
        $prompt .= "【出力形式】\n";
        $prompt .= "- 各項目は簡潔で具体的な内容にしてください（1行程度）\n";
        $prompt .= "- 5項目を改行区切りで出力してください\n";
        $prompt .= "- 番号や箇条書き記号（・、-、*など）は不要です\n";
        $prompt .= "- 各項目は独立した内容にしてください\n";
        
        if ($existingContent && trim($existingContent) !== '') {
            $prompt .= "\n";
            $prompt .= "既存の入力内容を参考にしつつ、より良い内容に改善してください。\n";
        }

        return $prompt;
    }

    /**
     * 自己診断結果をフォーマット
     */
    private function formatAssessmentData(string $type, array $data): ?string
    {
        switch ($type) {
            case 'mbti':
                return $data['type'] ?? null;
            case 'strengthsfinder':
                $top5 = $data['top5'] ?? [];
                return implode(', ', $top5);
            case 'enneagram':
                $result = "タイプ{$data['type']}";
                if (isset($data['wing'])) {
                    $result .= " (翼: {$data['wing']})";
                }
                return $result;
            case 'big5':
                $traits = [];
                if (isset($data['openness'])) $traits[] = "開放性: {$data['openness']}";
                if (isset($data['conscientiousness'])) $traits[] = "誠実性: {$data['conscientiousness']}";
                if (isset($data['extraversion'])) $traits[] = "外向性: {$data['extraversion']}";
                if (isset($data['agreeableness'])) $traits[] = "協調性: {$data['agreeableness']}";
                if (isset($data['neuroticism'])) $traits[] = "神経症傾向: {$data['neuroticism']}";
                return implode(', ', $traits);
            default:
                return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * AIの応答をフォーマット（5項目に整理）
     */
    private function formatResponse(string $response): string
    {
        // 改行で分割
        $lines = explode("\n", trim($response));
        
        // 空行や番号、箇条書き記号を除去
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // 番号や箇条書き記号を除去
            $line = preg_replace('/^[0-9]+[\.\)、]?\s*/u', '', $line);
            $line = preg_replace('/^[・\-*]\s*/u', '', $line);
            $line = trim($line);
            
            if (!empty($line)) {
                $items[] = $line;
            }
        }
        
        // 最大5項目に制限
        $items = array_slice($items, 0, 5);
        
        // 空の場合は元の応答を返す
        if (empty($items)) {
            return trim($response);
        }
        
        return implode("\n", $items);
    }
}
<?php

namespace App\Services;

use App\Models\Diary;
use App\Services\BedrockService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ContextDetectionService
{
    protected BedrockService $bedrockService;

    // 検出可能なコンテキスト
    const CONTEXTS = [
        'work' => '仕事',
        'family' => '家族',
        'hobby' => '趣味',
        'health' => '健康',
        'learning' => '学習',
        'relationship' => '人間関係',
        'other' => 'その他',
    ];

    public function __construct(BedrockService $bedrockService)
    {
        $this->bedrockService = $bedrockService;
    }

    /**
     * 日記内容からコンテキストを検出
     * 
     * @param string $diaryContent 日記の内容
     * @return string|null 検出されたコンテキスト（work, family, hobby, health, learning, relationship, other）
     */
    public function detectContext(string $diaryContent): ?string
    {
        if (empty(trim($diaryContent))) {
            return null;
        }

        // 簡易的なキーワードマッチング（高速化のため）
        $context = $this->detectContextByKeywords($diaryContent);
        
        // キーワードマッチングで検出できない場合はAIを使用
        if ($context === null) {
            $context = $this->detectContextByAI($diaryContent);
        }

        return $context ?? 'other';
    }

    /**
     * キーワードベースのコンテキスト検出（高速版）
     */
    protected function detectContextByKeywords(string $content): ?string
    {
        $keywords = [
            'work' => ['仕事', '職場', '会社', 'プロジェクト', '会議', '上司', '部下', '同僚', '業務', '営業', '開発', '企画', '打ち合わせ', '残業', '出社', '在宅', 'リモート'],
            'family' => ['家族', '家族', '両親', '親', '母', '父', '子供', '子ども', '息子', '娘', '兄弟', '姉妹', '祖父母', '実家', '家族旅行', '家族で'],
            'hobby' => ['趣味', '読書', '映画', '音楽', 'ゲーム', 'スポーツ', '旅行', 'カフェ', '散歩', '料理', '手芸', '写真', 'アート', '創作'],
            'health' => ['健康', '運動', 'ジム', 'ランニング', 'ウォーキング', 'ダイエット', '食事', '睡眠', '体調', '病院', '治療', 'メンタル', 'ストレス'],
            'learning' => ['学習', '勉強', '学び', '読書', '本', '講座', 'セミナー', '研修', '資格', 'スキル', '向上', '成長', '知識'],
            'relationship' => ['友達', '友人', '友達', '恋人', 'パートナー', '結婚', 'デート', '飲み会', 'パーティー', '集まり', 'コミュニティ'],
        ];

        $contentLower = mb_strtolower($content);

        foreach ($keywords as $context => $contextKeywords) {
            foreach ($contextKeywords as $keyword) {
                if (mb_strpos($contentLower, $keyword) !== false) {
                    return $context;
                }
            }
        }

        return null;
    }

    /**
     * AIを使用したコンテキスト検出
     */
    protected function detectContextByAI(string $content): ?string
    {
        $prompt = "以下の日記の内容を分析して、最も適切なコンテキストを1つ選んでください。\n\n";
        $prompt .= "【日記の内容】\n";
        $prompt .= mb_substr($content, 0, 500) . "\n\n";
        $prompt .= "【選択肢】\n";
        foreach (self::CONTEXTS as $key => $label) {
            if ($key !== 'other') {
                $prompt .= "- {$key}: {$label}\n";
            }
        }
        $prompt .= "\n";
        $prompt .= "最も適切なコンテキストを1つだけ選んで、そのキー（work, family, hobby, health, learning, relationship のいずれか）を回答してください。\n";
        $prompt .= "該当するものがなければ「other」と回答してください。\n";
        $prompt .= "回答はキーのみ（例: work）でお願いします:";

        try {
            $response = $this->bedrockService->chat(
                $prompt,
                [],
                config('bedrock.reflection_system_prompt')
            );

            if ($response) {
                $response = trim($response);
                // レスポンスからキーを抽出
                $response = mb_strtolower($response);
                
                // 有効なコンテキストかチェック
                if (array_key_exists($response, self::CONTEXTS)) {
                    return $response;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to detect context by AI', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * 各コンテキストの記録量を追跡
     * 
     * @param int $userId ユーザーID
     * @return array 各コンテキストの記録数
     */
    public function trackContextCount(int $userId): array
    {
        $diaries = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get();

        $contextCounts = array_fill_keys(array_keys(self::CONTEXTS), 0);

        foreach ($diaries as $diary) {
            $context = $this->detectContext($diary->content);
            if ($context && isset($contextCounts[$context])) {
                $contextCounts[$context]++;
            }
        }

        return $contextCounts;
    }

    /**
     * 特定コンテキストの日記を取得
     * 
     * @param int $userId ユーザーID
     * @param string $context コンテキスト（work, family, hobby, etc.）
     * @param int|null $limit 取得件数制限
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDiariesByContext(int $userId, string $context, ?int $limit = null)
    {
        $diaries = Diary::where('user_id', $userId)
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('date', 'desc')
            ->get();

        $filteredDiaries = $diaries->filter(function ($diary) use ($context) {
            $detectedContext = $this->detectContext($diary->content);
            return $detectedContext === $context;
        });

        if ($limit !== null) {
            return $filteredDiaries->take($limit);
        }

        return $filteredDiaries;
    }

    /**
     * コンテキストの日本語ラベルを取得
     * 
     * @param string $context コンテキストキー
     * @return string 日本語ラベル
     */
    public static function getContextLabel(string $context): string
    {
        return self::CONTEXTS[$context] ?? 'その他';
    }

    /**
     * コンテキストが生成可能かチェック（一定量の記録があるか）
     * 
     * @param int $userId ユーザーID
     * @param string $context コンテキスト
     * @param int $minCount 最小記録数（デフォルト: 5）
     * @return bool
     */
    public function canGenerateContextualManual(int $userId, string $context, int $minCount = 5): bool
    {
        $contextCounts = $this->trackContextCount($userId);
        
        return isset($contextCounts[$context]) && $contextCounts[$context] >= $minCount;
    }
}










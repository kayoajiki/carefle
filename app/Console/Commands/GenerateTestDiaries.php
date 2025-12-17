<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;

class GenerateTestDiaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate-diaries {email?} {--days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'テスト用の日記データを生成します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $days = (int) $this->option('days');

        if (!$email) {
            $email = $this->ask('ユーザーのメールアドレスを入力してください');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("ユーザーが見つかりません: {$email}");
            return 1;
        }

        $this->info("ユーザー: {$user->name} ({$user->email})");
        $this->info("{$days}日分の日記データを生成します...");

        // 過去N日間の日記を生成
        $generated = 0;
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // 既に日記が存在するかチェック
            $existing = Diary::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if ($existing) {
                $this->warn("  {$date->format('Y-m-d')}: 既に存在します（スキップ）");
                continue;
            }

            // テスト用の日記データを生成
            Diary::create([
                'user_id' => $user->id,
                'date' => $date,
                'motivation' => rand(50, 90),
                'content' => $this->generateTestContent($date),
            ]);

            $this->info("  {$date->format('Y-m-d')}: 生成しました");
            $generated++;
        }

        $this->info("\n完了しました！ {$generated}件の日記を生成しました。");

        // 進捗を更新
        $progressService = app(\App\Services\OnboardingProgressService::class);
        if ($generated >= 7) {
            $progressService->updateProgress($user->id, 'diary_7days');
            $this->info("7日間記録の進捗を更新しました。");
        }

        return 0;
    }

    /**
     * テスト用の日記内容を生成
     */
    protected function generateTestContent($date): string
    {
        $templates = [
            "今日は{$date->format('n月j日')}です。新しい気づきがありました。",
            "{$date->format('n月j日')}の振り返り。今日も成長できたと思います。",
            "今日は{$date->format('n月j日')}。仕事で良い成果が出ました。",
            "{$date->format('n月j日')}の日記。家族との時間が充実していました。",
            "今日は{$date->format('n月j日')}。新しい学びがありました。",
        ];

        return $templates[array_rand($templates)];
    }
}

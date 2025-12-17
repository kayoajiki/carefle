<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OnboardingProgress;
use App\Models\Diagnosis;
use App\Models\Diary;
use App\Services\OnboardingProgressService;

class ResetOnboardingProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:reset-onboarding {email?} {--step=diagnosis : リセットするステップ (diagnosis, diary_first, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'オンボーディング進捗をリセットしてテスト用の状態にします';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $step = $this->option('step');

        if (!$email) {
            $email = $this->ask('ユーザーのメールアドレスを入力してください');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("ユーザーが見つかりません: {$email}");
            return 1;
        }

        $this->info("ユーザー: {$user->name} ({$user->email})");

        $progressService = app(OnboardingProgressService::class);
        $progress = $progressService->getOrCreateProgress($user->id);

        switch ($step) {
            case 'diagnosis':
                // 診断ステップをリセット
                $completedSteps = $progress->completed_steps ?? [];
                $completedSteps = array_filter($completedSteps, fn($s) => $s !== 'diagnosis');
                $progress->completed_steps = array_values($completedSteps);
                $progress->current_step = 'diagnosis';
                $progress->last_prompted_at = null;
                $progress->save();
                
                // 完了済みの診断を削除（オプション）
                if ($this->confirm('完了済みの診断データも削除しますか？', false)) {
                    Diagnosis::where('user_id', $user->id)
                        ->where('is_completed', true)
                        ->delete();
                    $this->info('完了済みの診断データを削除しました。');
                }
                
                $this->info('診断ステップをリセットしました。');
                break;

            case 'diary_first':
                // 初回日記ステップをリセット
                $completedSteps = $progress->completed_steps ?? [];
                $completedSteps = array_filter($completedSteps, fn($s) => $s !== 'diary_first');
                $progress->completed_steps = array_values($completedSteps);
                $progress->current_step = 'diary_first';
                $progress->last_prompted_at = null;
                $progress->save();
                
                // 今日の日記を削除（オプション）
                if ($this->confirm('今日の日記データも削除しますか？', false)) {
                    Diary::where('user_id', $user->id)
                        ->whereDate('date', today())
                        ->delete();
                    $this->info('今日の日記データを削除しました。');
                }
                
                $this->info('初回日記ステップをリセットしました。');
                break;

            case 'all':
                // すべての進捗をリセット
                $progress->completed_steps = [];
                $progress->current_step = 'diagnosis';
                $progress->last_prompted_at = null;
                $progress->started_at = now();
                $progress->completed_at = null;
                $progress->save();
                
                $this->info('すべてのオンボーディング進捗をリセットしました。');
                break;

            default:
                $this->error("無効なステップ: {$step}");
                $this->info('利用可能なステップ: diagnosis, diary_first, all');
                return 1;
        }

        $this->info("\n現在の進捗状態:");
        $this->info("  完了ステップ: " . implode(', ', $progress->completed_steps ?? []));
        $this->info("  現在のステップ: " . ($progress->current_step ?? 'なし'));
        $this->info("  最後のプロンプト表示: " . ($progress->last_prompted_at ? $progress->last_prompted_at->format('Y-m-d H:i:s') : 'なし'));

        return 0;
    }
}

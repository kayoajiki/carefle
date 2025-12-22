<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {email?} {--force : 確認プロンプトをスキップ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定されたメールアドレスのテストユーザーを削除します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $force = $this->option('force');

        // メールアドレスの取得
        if (!$email) {
            $email = $this->ask('削除するユーザーのメールアドレスを入力してください');
        }

        // メールアドレスのバリデーション
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("無効なメールアドレスです: {$email}");
            return 1;
        }

        // ユーザーの検索
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("メールアドレス '{$email}' のユーザーが見つかりません。");
            return 1;
        }

        // 管理者ユーザーの場合は警告
        if ($user->is_admin) {
            $this->warn("⚠️  警告: このユーザーは管理者権限を持っています。");
            if (!$this->confirm('本当に削除しますか？', false)) {
                $this->info('削除をキャンセルしました。');
                return 0;
            }
        }

        // ユーザー情報の表示
        $this->displayUserInfo($user);

        // 関連データの確認
        $relatedDataCount = $this->getRelatedDataCount($user);
        if ($relatedDataCount['total'] > 0) {
            $this->info("\n関連データ:");
            $this->table(
                ['データ種別', '件数'],
                [
                    ['日記', $relatedDataCount['diaries']],
                    ['診断', $relatedDataCount['diagnoses']],
                    ['自己診断', $relatedDataCount['assessments']],
                    ['WCMシート', $relatedDataCount['wcmSheets']],
                    ['マイルストーン', $relatedDataCount['milestones']],
                    ['アクティビティログ', $relatedDataCount['activityLogs']],
                ]
            );
        }

        // 本番環境では確認を必須にする
        $isProduction = app()->environment('production');
        if ($isProduction && !$force) {
            $this->warn("\n⚠️  本番環境での実行です。");
            if (!$this->confirm('本当にこのユーザーを削除しますか？', false)) {
                $this->info('削除をキャンセルしました。');
                return 0;
            }
        } elseif (!$force) {
            if (!$this->confirm('このユーザーを削除しますか？', true)) {
                $this->info('削除をキャンセルしました。');
                return 0;
            }
        }

        // 削除実行
        try {
            DB::beginTransaction();

            $userId = $user->id;

            // アクティビティログに記録（削除前に記録）
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logUserAccountDeleted($userId);

            // ユーザーを削除（関連データは外部キー制約で自動削除される可能性がある）
            $user->delete();

            DB::commit();

            $this->info("✓ ユーザー '{$email}' を削除しました。");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("ユーザーの削除に失敗しました: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * ユーザー情報を表示
     */
    protected function displayUserInfo(User $user): void
    {
        $this->info("\n削除対象ユーザー:");
        $this->table(
            ['項目', '値'],
            [
                ['ID', $user->id],
                ['名前', $user->name],
                ['メールアドレス', $user->email],
                ['管理者権限', $user->is_admin ? '✓ はい' : '✗ いいえ'],
                ['登録日時', $user->created_at->format('Y-m-d H:i:s')],
                ['最終ログイン', $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '未ログイン'],
            ]
        );
    }

    /**
     * 関連データの件数を取得
     */
    protected function getRelatedDataCount(User $user): array
    {
        $counts = [
            'diaries' => $user->diaries()->count(),
            'diagnoses' => \App\Models\Diagnosis::where('user_id', $user->id)->count(),
            'assessments' => \App\Models\PersonalityAssessment::where('user_id', $user->id)->count(),
            'wcmSheets' => \App\Models\WcmSheet::where('user_id', $user->id)->count(),
            'milestones' => \App\Models\CareerMilestone::where('user_id', $user->id)->count(),
            'activityLogs' => $user->activityLogs()->count(),
        ];

        $counts['total'] = array_sum($counts);

        return $counts;
    }
}

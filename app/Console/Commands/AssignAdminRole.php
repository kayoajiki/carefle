<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:assign {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定されたメールアドレスの既存ユーザーに管理者権限を付与します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        
        // メールアドレスのバリデーション
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("無効なメールアドレスです: {$email}");
            return 1;
        }

        // ユーザーの検索
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("メールアドレス '{$email}' のユーザーが見つかりません。");
            $this->info("ユーザーが存在することを確認してください。");
            return 1;
        }

        // 既に管理者権限を持っているかチェック
        if ($user->is_admin) {
            $this->warn("ユーザー '{$email}' は既に管理者権限を持っています。");
            $this->displayUserInfo($user);
            return 0;
        }

        // 管理者権限を付与
        try {
            $user->update(['is_admin' => true]);
            $this->info("✓ ユーザー '{$email}' に管理者権限を付与しました。");
            $this->newLine();
            $this->displayUserInfo($user);
            return 0;
        } catch (\Exception $e) {
            $this->error("管理者権限の付与に失敗しました: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * ユーザー情報を表示
     */
    protected function displayUserInfo(User $user): void
    {
        $this->table(
            ['項目', '値'],
            [
                ['ID', $user->id],
                ['名前', $user->name],
                ['メールアドレス', $user->email],
                ['管理者権限', $user->is_admin ? '✓ はい' : '✗ いいえ'],
                ['作成日時', $user->created_at->format('Y-m-d H:i:s')],
                ['最終ログイン', $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '未ログイン'],
            ]
        );
    }
}

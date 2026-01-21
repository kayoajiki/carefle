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
    protected $signature = 'admin:assign {identifier?} {--id=} {--use-env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定されたメールアドレスまたはIDの既存ユーザーに管理者権限を付与します。--use-envオプションで環境変数から自動設定も可能';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // --use-envオプションが指定された場合、環境変数からIDを取得
        if ($this->option('use-env')) {
            $userId = env('ADMIN_USER_ID');
            if (!$userId) {
                $this->error('環境変数 ADMIN_USER_ID が設定されていません。');
                $this->info('.envファイルに ADMIN_USER_ID=1 のように設定してください。');
                return 1;
            }
            
            $user = User::find($userId);
            if (!$user) {
                $this->error("環境変数 ADMIN_USER_ID={$userId} のユーザーが見つかりません。");
                return 1;
            }
        } else {
            // --idオプションが指定された場合
            if ($this->option('id')) {
                $userId = $this->option('id');
                if (!is_numeric($userId)) {
                    $this->error("無効なIDです: {$userId}");
                    return 1;
                }
                
                $user = User::find($userId);
                if (!$user) {
                    $this->error("ID {$userId} のユーザーが見つかりません。");
                    return 1;
                }
            } else {
                // 引数が指定された場合（メールアドレスまたはID）
                $identifier = $this->argument('identifier');
                if (!$identifier) {
                    $this->error('メールアドレス、ID、または --id オプション、--use-env オプションを指定してください。');
                    $this->info('使用例:');
                    $this->info('  php artisan admin:assign user@example.com');
                    $this->info('  php artisan admin:assign --id=1');
                    $this->info('  php artisan admin:assign --use-env');
                    return 1;
                }
                
                // 数値の場合はIDとして扱う
                if (is_numeric($identifier)) {
                    $user = User::find($identifier);
                    if (!$user) {
                        $this->error("ID {$identifier} のユーザーが見つかりません。");
                        return 1;
                    }
                } else {
                    // メールアドレスとして扱う
                    if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                        $this->error("無効なメールアドレスまたはIDです: {$identifier}");
                        return 1;
                    }
                    
                    $user = User::where('email', $identifier)->first();
                    if (!$user) {
                        $this->error("メールアドレス '{$identifier}' のユーザーが見つかりません。");
                        $this->info("ユーザーが存在することを確認してください。");
                        return 1;
                    }
                }
            }
        }

        // 既に管理者権限を持っているかチェック
        if ($user->is_admin) {
            $this->warn("ユーザー（ID: {$user->id}, Email: {$user->email}）は既に管理者権限を持っています。");
            $this->displayUserInfo($user);
            return 0;
        }

        // 管理者権限を付与
        try {
            $user->update(['is_admin' => true]);
            $this->info("✓ ユーザー（ID: {$user->id}, Email: {$user->email}）に管理者権限を付与しました。");
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

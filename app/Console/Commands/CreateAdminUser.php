<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user {email?} {--name=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '管理者ユーザーを作成します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 管理者ユーザー作成 ===');
        $this->newLine();

        // メールアドレスの取得
        $email = $this->argument('email');
        if (!$email) {
            $email = $this->ask('メールアドレスを入力してください');
        }

        // バリデーション
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->error('無効なメールアドレスです。');
            return 1;
        }

        // 既存ユーザーのチェック
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if ($existingUser->is_admin) {
                $this->warn('このメールアドレスは既に管理者として登録されています。');
                return 1;
            }

            // 既存ユーザーを管理者に昇格
            if ($this->confirm('このメールアドレスのユーザーは既に存在します。管理者権限を付与しますか？', true)) {
                $existingUser->is_admin = true;
                $existingUser->save();
                $this->info('✓ 既存ユーザーに管理者権限を付与しました。');
                $this->displayUserInfo($existingUser);
                return 0;
            } else {
                $this->info('キャンセルしました。');
                return 1;
            }
        }

        // 新規ユーザーの作成
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('ユーザー名を入力してください');
        }

        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('パスワードを入力してください（8文字以上）');
            $passwordConfirm = $this->secret('パスワードを再入力してください');

            if ($password !== $passwordConfirm) {
                $this->error('パスワードが一致しません。');
                return 1;
            }
        }

        // パスワードのバリデーション
        if (strlen($password) < 8) {
            $this->error('パスワードは8文字以上である必要があります。');
            return 1;
        }

        // ユーザーの作成
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
                'profile_completed' => true,
            ]);

            $this->newLine();
            $this->info('✓ 管理者ユーザーを作成しました！');
            $this->displayUserInfo($user);

            return 0;
        } catch (\Exception $e) {
            $this->error('ユーザーの作成に失敗しました: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * ユーザー情報を表示
     */
    protected function displayUserInfo(User $user)
    {
        $this->newLine();
        $this->table(
            ['項目', '値'],
            [
                ['ID', $user->id],
                ['名前', $user->name],
                ['メールアドレス', $user->email],
                ['管理者権限', $user->is_admin ? 'はい' : 'いいえ'],
                ['作成日時', $user->created_at->format('Y-m-d H:i:s')],
            ]
        );
    }
}


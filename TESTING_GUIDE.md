# Phase 3 モーダルテストガイド

## テスト方法

### 方法1: 新規ユーザーでテスト（推奨）

1. **新規ユーザー登録**
   - アプリケーションに新規ユーザーとして登録
   - プロフィール情報を入力して完了させる（`profile_completed = true`）

2. **診断促進モーダルのテスト**
   - ダッシュボードにアクセス
   - 診断未完了の場合、診断促進モーダルが表示されることを確認
   - 「診断を続ける」ボタンをクリックして診断画面に遷移することを確認
   - 「後でやる」ボタンをクリックしてモーダルが閉じることを確認
   - 24時間以内は再表示されないことを確認

3. **日記促進モーダルのテスト**
   - 診断を完了させる
   - ダッシュボードにアクセス
   - 初回日記未記録の場合、日記促進モーダルが表示されることを確認
   - 「今日の振り返りを書く」ボタンをクリックして日記画面に遷移することを確認
   - 「後でやる」ボタンをクリックしてモーダルが閉じることを確認
   - 24時間以内は再表示されないことを確認

### 方法2: 既存ユーザーでテスト

既存のユーザーデータをリセットしてテストできます。

#### 診断促進モーダルのテスト

```bash
# 診断ステップをリセット
php artisan test:reset-onboarding test@example.com --step=diagnosis
```

その後、ダッシュボードにアクセスしてモーダルが表示されることを確認します。

#### 日記促進モーダルのテスト

```bash
# 初回日記ステップをリセット（診断は完了している必要があります）
php artisan test:reset-onboarding test@example.com --step=diary_first
```

その後、ダッシュボードにアクセスしてモーダルが表示されることを確認します。

#### すべての進捗をリセット

```bash
# すべてのオンボーディング進捗をリセット
php artisan test:reset-onboarding test@example.com --step=all
```

### 方法3: データベースを直接操作

Tinkerを使用して直接データを操作することもできます。

```bash
php artisan tinker
```

```php
// ユーザーを取得
$user = \App\Models\User::where('email', 'test@example.com')->first();

// オンボーディング進捗をリセット
$progress = \App\Models\OnboardingProgress::where('user_id', $user->id)->first();
$progress->completed_steps = [];
$progress->current_step = 'diagnosis';
$progress->last_prompted_at = null;
$progress->save();

// 診断データを削除（オプション）
\App\Models\Diagnosis::where('user_id', $user->id)->where('is_completed', true)->delete();

// 今日の日記を削除（オプション）
\App\Models\Diary::where('user_id', $user->id)->whereDate('date', today())->delete();
```

## テストチェックリスト

### 診断促進モーダル

- [ ] プロフィール完了済みユーザーで表示される
- [ ] 診断未完了の場合のみ表示される
- [ ] 診断完了済みの場合は表示されない
- [ ] 「診断を続ける」ボタンで診断画面に遷移
- [ ] 「後でやる」ボタンでモーダルが閉じる
- [ ] 「後でやる」選択後、24時間以内は再表示されない
- [ ] 24時間経過後に再表示される（手動で`last_prompted_at`を更新してテスト）

### 日記促進モーダル

- [ ] プロフィール完了済みユーザーで表示される
- [ ] 診断完了済みの場合のみ表示される
- [ ] 初回日記未記録の場合のみ表示される
- [ ] 今日の日記が既に存在する場合は表示されない
- [ ] 「今日の振り返りを書く」ボタンで日記画面に遷移
- [ ] 「後でやる」ボタンでモーダルが閉じる
- [ ] 「後でやる」選択後、24時間以内は再表示されない
- [ ] 24時間経過後に再表示される（手動で`last_prompted_at`を更新してテスト）

## トラブルシューティング

### モーダルが表示されない場合

1. **プロフィールが完了しているか確認**
   ```php
   $user = \App\Models\User::where('email', 'test@example.com')->first();
   $user->profile_completed; // true である必要があります
   ```

2. **オンボーディング進捗を確認**
   ```php
   $progress = \App\Services\OnboardingProgressService::class;
   $progressService = app($progress);
   $progressService->checkStepCompletion($user->id, 'diagnosis'); // false である必要があります
   ```

3. **最後のプロンプト表示時刻を確認**
   ```php
   $progress = \App\Models\OnboardingProgress::where('user_id', $user->id)->first();
   $progress->last_prompted_at; // null または 24時間以上前である必要があります
   ```

4. **ブラウザのコンソールでエラーを確認**
   - 開発者ツールを開いてエラーがないか確認
   - Alpine.jsが正しく読み込まれているか確認

### モーダルが閉じない場合

1. **Alpine.jsが正しく読み込まれているか確認**
2. **Livewireが正しく動作しているか確認**
3. **ブラウザのキャッシュをクリア**







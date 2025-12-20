# オンボーディングステップ1・2の実装詳細（修正版）

## Phase 3.1: ステップ1 - 現職満足度診断の促進

### 初回ログイン時の挙動
- **実装箇所**: `app/Http/Responses/RegisterResponse.php`
- 初回ログイン時は自動で診断画面（`diagnosis.start`）にリダイレクト（現在の仕様を維持）
- 診断は途中保存可能で、いつでもダッシュボードに戻れる

### ダッシュボードでの促し
- **実装箇所**: `app/Http/Controllers/DashboardController.php`、`resources/views/dashboard.blade.php`
- 診断未完了ユーザーに対して、控えめなモーダル/オーバーレイで診断を促す
- **モーダル/オーバーレイの内容**:
  - 「診断を続ける」ボタンと「後でやる」ボタンを表示
  - 一度「後でやる」を選択したら、しばらく表示しない（しつこくしない）
  - 表示頻度の制御: ユーザーの`OnboardingProgress`モデルに`last_prompted_at`フィールドを追加し、24時間以内は再表示しない
- 診断完了時に `OnboardingProgressService` で進捗更新

### 実装詳細
- **Livewireコンポーネント**: `app/Livewire/DiagnosisPromptModal.php`（新規作成）
  - 診断未完了かつ「後でやる」を選択してから24時間経過している場合のみ表示
  - モーダル/オーバーレイの表示/非表示を制御
- **データベース**: `onboarding_progress`テーブルに`last_prompted_at`カラムを追加

## Phase 3.2: ステップ2 - 初回日記記録の促進

### ダッシュボードでの促し
- **実装箇所**: `app/Http/Controllers/DashboardController.php`、`resources/views/dashboard.blade.php`
- 診断完了かつ初回日記未記録の場合、ダッシュボード上にオーバーレイ/モーダルで「今日の振り返りを書く」を表示
- **モーダル/オーバーレイの内容**:
  - 「今日の振り返りを書く」ボタンを表示
  - 「後でやる」ボタンも表示（一度選択したらしばらく表示しない）
  - 表示頻度の制御: ステップ1と同様に`last_prompted_at`で制御

### 日記記録の体験
- **実装箇所**: 既存の日記記録機能を使用
- 診断結果を参照した日記テーマ提案は**不要**
- 通常の日記記録フォームを使用（`app/Livewire/DiaryForm.php`、`resources/views/livewire/diary-form.blade.php`）

### フィードバック
- **実装箇所**: 既存のAIフィードバック機能を使用
- 日記保存後のフィードバックは既存のAIフィードバックを使用（`app/Livewire/DiaryReflectionFeedback.php`）
- 特別な「めちゃ褒める」機能は追加しない

### 進捗更新
- 初回日記保存時に `OnboardingProgressService` で進捗更新
- 次のアクション: 「自己診断結果を入力すると、もっと深い分析ができます」と案内

### 実装詳細
- **Livewireコンポーネント**: `app/Livewire/DiaryPromptModal.php`（新規作成）
  - 診断完了かつ初回日記未記録の場合のみ表示
  - モーダル/オーバーレイの表示/非表示を制御
- **データベース**: `onboarding_progress`テーブルの`last_prompted_at`を使用（ステップ1と共有）

## 共通実装

### モーダル/オーバーレイのデザイン
- 控えめなデザイン（しつこくない）
- 半透明の背景
- 中央に配置されたカード形式
- アニメーション: フェードイン/フェードアウト

### 表示制御ロジック
- `OnboardingProgressService`に`shouldShowPrompt($step)`メソッドを追加
- 各ステップの完了状態と`last_prompted_at`をチェック
- 24時間以内に表示した場合は再表示しない





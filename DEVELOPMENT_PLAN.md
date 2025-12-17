# キャリフレ改善計画：開発プラン（最終版）

## プロジェクト概要

競合調査の知見を踏まえ、ユーザーの自己理解欲求を刺激し、継続利用を促進するオンボーディングフローを実装します。ゲーミフィケーション要素を取り入れ、達成感と成長実感を可視化し、最終的には過去→現在→未来のマッピングが完成するシステムを構築します。

### 核となるビジョン
- 最初は本質だけを提示（プチ取説）
- ユーザーが入力内容を増やすことで、過去→現在→未来のマッピングが完成
- コンテキスト別（仕事、家族など）の取説が曼荼羅形式で段階的に生成
- キャリフレに戻ってくれば自分を思い出し、変容も見つめ直せる
- 将来的にナノバナナで画像化

---

## 開発フェーズと実装順序

### Phase 1: オンボーディング進捗管理システム（基盤構築）

#### 1.1 データベース設計
**マイグレーション**: `database/migrations/YYYY_MM_DD_HHMMSS_create_onboarding_progress_table.php`

**テーブル構造**:
```php
- id (bigint, primary key)
- user_id (bigint, foreign key → users.id)
- current_step (string) // 現在のステップ
- completed_steps (json) // 完了したステップの配列
- last_prompted_at (timestamp, nullable) // 最後にプロンプトを表示した日時
- started_at (timestamp, nullable)
- completed_at (timestamp, nullable)
- created_at, updated_at
```

**ステップ定義**:
- `diagnosis`: 現職満足度診断
- `diary_first`: 初回日記
- `assessment`: 自己診断入力
- `wcm_created`: WCMシート作成
- `diary_7days`: 7日間記録
- `manual_generated`: プチ取説生成

#### 1.2 モデル実装
**ファイル**: `app/Models/OnboardingProgress.php`

**主要メソッド**:
- `isStepCompleted($step)`: ステップ完了判定
- `getCurrentStep()`: 現在のステップ取得
- `markStepCompleted($step)`: ステップ完了マーク
- `isOnboardingComplete()`: オンボーディング完了判定

#### 1.3 進捗追跡サービス
**ファイル**: `app/Services/OnboardingProgressService.php`

**主要メソッド**:
- `checkStepCompletion($userId, $step)`: 各ステップの完了状態をチェック
- `getNextStep($userId)`: 次のステップを取得
- `updateProgress($userId, $step)`: 進捗を更新
- `shouldShowPrompt($userId, $step)`: プロンプトを表示すべきか判定（24時間以内に表示した場合は再表示しない）

---

### Phase 2: ダッシュボード進捗表示とガイド

#### 2.1 進捗バーコンポーネント
**ファイル**: `app/Livewire/OnboardingProgressBar.php`

**機能**:
- ダッシュボード上部に控えめな進捗バーを表示
- 各ステップのアイコンと進捗率を表示
- 完了したステップはチェックマーク、未完了はグレーアウト
- クリックで各ステップの詳細ページへ遷移
- **重要**: オンボーディング完了後は非表示（達成バッジのみ表示）

**ビュー**: `resources/views/livewire/onboarding-progress-bar.blade.php`

#### 2.2 機能の段階的提示システム
**ファイル**: `app/Services/FeatureDiscoveryService.php`

**機能**:
- ユーザーの利用状況に応じて機能を段階的にアンロック
- サイドバーメニューの表示制御（未アンロック機能は非表示または「ロック」表示）
- 機能発見のタイミングでツールチップやバッジを表示

#### 2.3 サイドバーメニューの動的制御
**ファイル**: `resources/views/components/layouts/app/sidebar.blade.php`（更新）

**機能**:
- オンボーディング未完了時は最小限のメニューのみ表示
- 進捗に応じて機能を段階的に表示
- 「こんなこともできます」という案内を適切なタイミングで表示

---

### Phase 3: ステップ別導線実装

#### 3.1 ステップ1: 現職満足度診断の促進

**初回ログイン時の挙動**:
- **ファイル**: `app/Http/Responses/RegisterResponse.php`（更新）
- 初回ログイン時は自動で診断画面（`diagnosis.start`）にリダイレクト（現在の仕様を維持）
- 診断は途中保存可能で、いつでもダッシュボードに戻れる

**ダッシュボードでの促し**:
- **ファイル**: 
  - `app/Http/Controllers/DashboardController.php`（更新）
  - `resources/views/dashboard.blade.php`（更新）
  - `app/Livewire/DiagnosisPromptModal.php`（新規作成）

**DiagnosisPromptModalの機能**:
- 診断未完了かつ「後でやる」を選択してから24時間経過している場合のみ表示
- モーダル/オーバーレイの表示/非表示を制御
- 「診断を続ける」ボタンと「後でやる」ボタンを表示
- 一度「後でやる」を選択したら、24時間は再表示しない（しつこくしない）
- 控えめなデザイン（半透明の背景、中央に配置されたカード形式、フェードイン/フェードアウト）

**進捗更新**:
- 診断完了時に `OnboardingProgressService::updateProgress()` で進捗更新

#### 3.2 ステップ2: 初回日記記録の促進

**ダッシュボードでの促し**:
- **ファイル**: 
  - `app/Http/Controllers/DashboardController.php`（更新）
  - `resources/views/dashboard.blade.php`（更新）
  - `app/Livewire/DiaryPromptModal.php`（新規作成）

**DiaryPromptModalの機能**:
- 診断完了かつ初回日記未記録の場合のみ表示
- モーダル/オーバーレイの表示/非表示を制御
- 「今日の振り返りを書く」ボタンと「後でやる」ボタンを表示
- 一度「後でやる」を選択したら、24時間は再表示しない
- ステップ1と同様の控えめなデザイン

**日記記録の体験**:
- **ファイル**: 既存の日記記録機能を使用
  - `app/Livewire/DiaryForm.php`
  - `resources/views/livewire/diary-form.blade.php`
- 診断結果を参照した日記テーマ提案は**不要**
- 通常の日記記録フォームを使用

**フィードバック**:
- **ファイル**: 既存のAIフィードバック機能を使用
  - `app/Livewire/DiaryReflectionFeedback.php`
- 日記保存後のフィードバックは既存のAIフィードバックを使用
- 特別な「めちゃ褒める」機能は追加しない

**進捗更新**:
- 初回日記保存時に `OnboardingProgressService::updateProgress()` で進捗更新
- 次のアクション: 「自己診断結果を入力すると、もっと深い分析ができます」と案内

#### 3.3 ステップ3: 自己診断結果入力促進
- **ファイル**: `app/Livewire/PersonalityAssessmentForm.php`（更新）
- 自己診断結果保存時に `OnboardingProgressService::updateProgress()` で進捗更新
- ダッシュボードで「自己診断結果を1つ入力するとプチ取説が生成されます」と表示

#### 3.4 ステップ4: WCMシート作成促進
- **ファイル**: `app/Livewire/WcmForm.php`（更新）
- WCMシート作成完了時に `OnboardingProgressService::updateProgress()` で進捗更新
- ダッシュボードで「WCMシートを作成すると、未来の自分が見えてきます」と表示
- オンボーディング中は「未来の自分を描く」という文脈でWCM作成を促す
- WCM完了時に「未来の自分が描けました！次は7日間の日記を書いてみましょう」と案内

---

### Phase 4: 日記記録のゲーミフィケーション

#### 4.1 日記記録カウンター
- **ファイル**: `app/Http/Controllers/DashboardController.php`（更新）
- 連続記録日数の表示（既存の `reflectionStreak` を活用）
- 7日間記録の進捗バー表示（例: 「あと3日でプチ取説が生成されます！」）

#### 4.2 日記保存時の褒め機能強化
- **ファイル**: `app/Livewire/DiaryReflectionFeedback.php`（更新）
- 日記保存時にAIが「めちゃ褒める」フィードバックを生成
- プロンプトを「あなたの日記は素晴らしい！○○な視点が素敵です。続けることで...」のような励まし重視に調整
- 連続記録日数に応じた特別なメッセージ（3日、7日、14日など）

#### 4.3 進捗可視化
- **ファイル**: `resources/views/dashboard.blade.php`（更新）
- 日記記録カレンダーをミニマップ形式で表示
- 7日間の記録状況を視覚的に表示
- 達成時にアニメーション演出

---

### Phase 5: プチ取説生成機能（本質のみ）

#### 5.1 診断結果統合分析サービス
**ファイル**: `app/Services/DiagnosisIntegrationService.php`（新規作成）

**機能**:
- 複数の診断結果（MBTI、ストレングスファインダー、現職満足度診断など）を横断分析
- 「共通して○○が強い」などの統合レポート生成
- AI（Bedrock）を使用して自然な文章で統合解釈を生成
- **重要**: 最初は本質だけを抽出（詳細は後から追加）

**主要メソッド**:
- `analyzeDiagnoses($userId)`: ユーザーの全診断結果を統合分析
- `generateIntegrationReport($diagnoses)`: 統合レポート生成

#### 5.2 日記分析サービス（初期版）
**ファイル**: `app/Services/DiaryAnalysisService.php`（新規作成）

**機能**:
- 7日間の日記を分析し、価値観、強み、成長ポイントを抽出
- AIを使用して日記の内容を統合し、ユーザーの傾向を分析
- **重要**: 最初は表面的な分析のみ（深掘りは後から）

**主要メソッド**:
- `analyzeDiaries($userId, $days = 7)`: 指定日数の日記を分析
- `extractValuesAndStrengths($diaries)`: 価値観と強みを抽出

#### 5.3 プチ取説生成サービス（本質のみ）
**ファイル**: `app/Services/MiniManualGeneratorService.php`（新規作成）

**機能**:
- `DiagnosisIntegrationService` と `DiaryAnalysisService` の結果を統合
- ユーザー専用の「プチ取説」を生成（HTML形式）
- **内容**: 本質のみ（統合診断結果の核心、日記から見える価値観の本質、強みの核心、次のステップ提案）
- 詳細な分析は後から段階的に追加

**主要メソッド**:
- `generateMiniManual($userId)`: プチ取説を生成
- `buildManualContent($diagnosisReport, $diaryReport)`: 取説コンテンツを構築

#### 5.4 プチ取説表示ページ
**ルート**: `GET /onboarding/mini-manual`（認証必須）
**コントローラー**: `app/Http/Controllers/OnboardingController.php`（新規作成）

**主要メソッド**:
- `showMiniManual()`: プチ取説を表示（認証必須）
- `generateMiniManual()`: プチ取説を生成（初回のみ、非同期処理を検討）

**ビュー**: `resources/views/onboarding/mini-manual.blade.php`（新規作成）
- 美しいレイアウトでプチ取説を表示
- **重要**: すべてのデータは非公開（認証必須で表示）
- PDFダウンロードボタン（主要な共有方法）
- SNSシェア案内（PDFをダウンロードしてSNSに投稿する形式）

#### 5.5 PDF生成機能（主要な共有方法）
**ライブラリ**: `barryvdh/laravel-dompdf` または `spatie/laravel-pdf`

**ファイル**: `app/Http/Controllers/OnboardingController.php`（更新）

**機能**:
- `downloadMiniManualPdf()`: PDFを生成してダウンロード
- プチ取説の内容をPDF形式で出力
- **重要**: PDFはSNSシェア用に最適化されたレイアウト
- PDFには「キャリフレで生成されたプチ取説」というクレジットを記載

#### 5.6 SNSシェア案内機能
**ファイル**: `resources/views/onboarding/mini-manual.blade.php`（更新）

**機能**:
- **基本方針**: データはすべて非公開、PDFをダウンロードしてSNSに投稿する形式
- PDFダウンロードボタンを目立つ位置に配置
- SNSシェア案内テキストを表示（例: 「PDFをダウンロードして、XやInstagramに投稿してみましょう」）
- 各SNSの投稿方法を簡単に案内
  - X（Twitter）: PDFを添付して投稿
  - Instagram: PDFを画像として投稿、またはPDFのスクリーンショットを投稿
  - Facebook: PDFを添付して投稿
- シェア用のテキストテンプレートを用意（「私のプチ取説が完成しました！#キャリフレ」など）

---

### Phase 6: コンテキスト別取説生成（段階的拡張）

#### 6.1 コンテキスト検出サービス
**ファイル**: `app/Services/ContextDetectionService.php`（新規作成）

**機能**:
- 日記内容を分析してコンテキストを自動検出（仕事、家族、趣味、健康など）
- AIを使用してコンテキストを分類
- 各コンテキストの記録量を追跡

**主要メソッド**:
- `detectContext($diaryContent)`: 日記内容からコンテキストを検出
- `trackContextCount($userId)`: 各コンテキストの記録量を追跡

#### 6.2 コンテキスト別取説生成サービス
**ファイル**: `app/Services/ContextualManualGeneratorService.php`（新規作成）

**機能**:
- 特定コンテキスト（例: 仕事）の日記が一定量（例: 5件以上）蓄積されたら生成
- 「仕事の自分」「家族の自分」などのコンテキスト別取説を生成
- プチ取説と同様の構造だが、コンテキスト特化の内容

#### 6.3 コンテキスト別取説表示
**ルート**: `GET /manual/context/{context}`
**コントローラー**: `app/Http/Controllers/ManualController.php`（新規作成）

**機能**:
- コンテキスト別取説を表示
- 複数のコンテキスト取説を一覧表示

---

### Phase 7: 曼荼羅形式のマッピング（過去→現在→未来）

#### 7.1 マッピングデータ構造
**マイグレーション**: `database/migrations/YYYY_MM_DD_HHMMSS_create_user_mappings_table.php`

**テーブル構造**:
```php
- id (bigint, primary key)
- user_id (bigint, foreign key → users.id)
- mapping_type (enum: past/current/future)
- context (string, nullable) // work/family/etc
- data (json) // マッピングデータ
- generated_at (timestamp)
- created_at, updated_at
```

**モデル**: `app/Models/UserMapping.php`（新規作成）

#### 7.2 マッピング生成サービス
**ファイル**: `app/Services/UserMappingService.php`（新規作成）

**機能**:
- 過去の記録（人生史、過去の日記）から「過去の自分」をマッピング
- 現在の記録（最新の診断、日記）から「現在の自分」をマッピング
- 未来の記録（WCM、マイルストーン）から「未来の自分」をマッピング
- 3つのマッピングを統合して曼荼羅形式で可視化
- **重要**: 曼荼羅の構造は外側=未来、中央=現在、内側=過去とする

**主要メソッド**:
- `generatePastMapping($userId)`: 過去の自分をマッピング
- `generateCurrentMapping($userId)`: 現在の自分をマッピング
- `generateFutureMapping($userId)`: 未来の自分をマッピング
- `integrateMappings($past, $current, $future)`: 3つのマッピングを統合

#### 7.3 曼荼羅形式の可視化
**ファイル**: `app/Livewire/UserMappingVisualization.php`（新規作成）

**機能**:
- 曼荼羅形式で表示（外側=未来、中央=現在、内側=過去）
- 外側の輪：未来の自分（WCMのWill/Can/Must、マイルストーン）
- 中央の輪：現在の自分（最新の診断結果、最近の日記）
- 内側の輪：過去の自分（過去の診断、過去の日記、人生史）
- 各コンテキスト（仕事、家族など）をセクションとして配置
- インタラクティブなマップ（クリックで詳細表示）

**ビュー**: `resources/views/livewire/user-mapping-visualization.blade.php`（新規作成）

#### 7.4 変容追跡機能
**ファイル**: `app/Services/TransformationTrackingService.php`（新規作成）

**機能**:
- 過去の自分と現在の自分を比較
- 変容ポイントをハイライト
- 成長グラフを生成

---

### Phase 8: 自分を思い出す機能

#### 8.1 過去の記録へのアクセス
**ファイル**: `resources/views/dashboard.blade.php`（更新）

**機能**:
- 「過去の自分を思い出す」セクションを追加
- 過去の日記、診断結果、取説へのクイックアクセス

#### 8.2 変容の可視化
**ファイル**: `resources/views/manual/mapping.blade.php`（新規作成）

**機能**:
- 過去→現在→未来の変容を時系列で表示
- 変容の大きさを視覚的に表示

---

### Phase 9: 将来的な拡張（ナノバナナ統合）

#### 9.1 ナノバナナ画像生成準備
**ファイル**: `app/Services/NanobananaImageService.php`（既存を拡張）

**機能**:
- 曼荼羅形式の取説を画像として生成
- カスタマイズ可能なスタイル

#### 9.2 画像化機能
**実装**: 将来的に実装
- 取説を画像としてエクスポート
- SNSシェア用の画像生成

---

## 実装優先順位とマイルストーン

### マイルストーン1: オンボーディング基盤（Phase 1-2）
**目標**: オンボーディング進捗管理システムとダッシュボード表示の実装
**期間**: 1-2週間
**成果物**:
- オンボーディング進捗管理テーブルとモデル
- 進捗追跡サービス
- 進捗バーコンポーネント
- 機能の段階的提示システム

### マイルストーン2: オンボーディング導線（Phase 3）
**目標**: ステップ1-4の導線実装
**期間**: 2-3週間
**成果物**:
- ステップ1: 診断促進モーダル
- ステップ2: 日記促進モーダル
- ステップ3: 自己診断入力促進
- ステップ4: WCM作成促進

### マイルストーン3: ゲーミフィケーション（Phase 4）
**目標**: 日記記録のゲーミフィケーション強化
**期間**: 1-2週間
**成果物**:
- 日記記録カウンター
- 褒め機能強化
- 進捗可視化

### マイルストーン4: プチ取説生成（Phase 5）
**目標**: プチ取説生成機能の実装
**期間**: 3-4週間
**成果物**:
- 診断結果統合分析サービス
- 日記分析サービス
- プチ取説生成サービス
- プチ取説表示ページ
- SNSシェア機能
- PDF生成機能

### マイルストーン5: コンテキスト別取説（Phase 6）
**目標**: コンテキスト別取説生成機能の実装
**期間**: 2-3週間
**成果物**:
- コンテキスト検出サービス
- コンテキスト別取説生成サービス
- コンテキスト別取説表示

### マイルストーン6: 曼荼羅マッピング（Phase 7）
**目標**: 曼荼羅形式のマッピング機能の実装
**期間**: 3-4週間
**成果物**:
- マッピングデータ構造
- マッピング生成サービス
- 曼荼羅形式の可視化
- 変容追跡機能

### マイルストーン7: 思い出す機能（Phase 8）
**目標**: 自分を思い出す機能の実装
**期間**: 1-2週間
**成果物**:
- 過去の記録へのアクセス
- 変容の可視化

### マイルストーン8: 将来的な拡張（Phase 9）
**目標**: ナノバナナ統合の準備
**期間**: 将来的に実装
**成果物**:
- ナノバナナ画像生成準備
- 画像化機能

---

## 技術的な考慮事項

### AI統合
- **AWS Bedrock**: 診断結果の統合分析と日記分析に使用
- **プロンプト設計**: 本質のみを抽出するよう最適化
- **エラーハンドリング**: AI生成失敗時のフォールバック処理を実装

### パフォーマンス
- **非同期処理**: プチ取説生成はジョブキュー（Laravel Queue）を使用
- **キャッシュ**: 生成済みプチ取説はキャッシュして再生成を避ける
- **データベース最適化**: インデックスを適切に設定

### セキュリティとプライバシー
- **データ暗号化**: ユーザーデータは暗号化し、ユーザー自身のみがアクセス可能
- **認証**: すべてのエンドポイントで認証チェック
- **入力検証**: すべてのユーザー入力を検証

### UX/UI
- **モーダル/オーバーレイ**: 控えめなデザイン（しつこくない）
- **アニメーション**: フェードイン/フェードアウト、達成時のアニメーション
- **レスポンシブデザイン**: モバイル対応

### 機能の段階的提示
- **混乱を避ける**: 必要に応じて機能をアンロック
- **ガイド**: 適切なタイミングで機能案内を表示

---

## ファイル一覧

### 新規作成ファイル（合計: 約20ファイル）

#### モデル
- `app/Models/OnboardingProgress.php`
- `app/Models/UserMapping.php`

#### サービス
- `app/Services/OnboardingProgressService.php`
- `app/Services/FeatureDiscoveryService.php`
- `app/Services/DiagnosisIntegrationService.php`
- `app/Services/DiaryAnalysisService.php`
- `app/Services/MiniManualGeneratorService.php`
- `app/Services/ContextDetectionService.php`
- `app/Services/ContextualManualGeneratorService.php`
- `app/Services/UserMappingService.php`
- `app/Services/TransformationTrackingService.php`

#### コントローラー
- `app/Http/Controllers/OnboardingController.php`
- `app/Http/Controllers/ManualController.php`

#### Livewireコンポーネント
- `app/Livewire/OnboardingProgressBar.php`
- `app/Livewire/UserMappingVisualization.php`
- `app/Livewire/DiagnosisPromptModal.php`
- `app/Livewire/DiaryPromptModal.php`

#### ビュー
- `resources/views/livewire/onboarding-progress-bar.blade.php`
- `resources/views/livewire/user-mapping-visualization.blade.php`
- `resources/views/livewire/diagnosis-prompt-modal.blade.php`
- `resources/views/livewire/diary-prompt-modal.blade.php`
- `resources/views/onboarding/mini-manual.blade.php`
- `resources/views/manual/context.blade.php`
- `resources/views/manual/mapping.blade.php`

#### マイグレーション
- `database/migrations/YYYY_MM_DD_HHMMSS_create_onboarding_progress_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_user_mappings_table.php`

### 更新ファイル（合計: 約10ファイル）

- `resources/views/dashboard.blade.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Livewire/DiaryReflectionFeedback.php`
- `resources/views/components/layouts/app/sidebar.blade.php`
- `resources/views/diagnosis/result.blade.php`
- `app/Livewire/PersonalityAssessmentForm.php`
- `app/Livewire/WcmForm.php`
- `app/Http/Responses/RegisterResponse.php`
- `routes/web.php`（ルート追加）

---

## 開発時の注意事項

### テスト
- 各フェーズでユニットテストと統合テストを実装
- オンボーディングフローのE2Eテストを実装

### ドキュメント
- 各サービスのメソッドにPHPDocを記述
- 複雑なロジックにはコメントを追加

### エラーハンドリング
- すべてのAI生成処理でtry-catchを実装
- ユーザーフレンドリーなエラーメッセージを表示

### パフォーマンステスト
- プチ取説生成のパフォーマンスを測定
- 必要に応じて最適化

---

## 次のステップ

1. **Phase 1-2の実装開始**: オンボーディング進捗管理システムとダッシュボード表示
2. **Phase 3の実装**: ステップ別導線実装
3. **Phase 4の実装**: 日記記録のゲーミフィケーション
4. **Phase 5の実装**: プチ取説生成機能
5. **Phase 6-9の実装**: 段階的に実装

---

## 参考資料

- `USER_JOURNEY.md`: ユーザージャーニーの詳細
- `ONBOARDING_STEPS_1_2.md`: ステップ1・2の実装詳細
- `.plan.md`: 計画の詳細


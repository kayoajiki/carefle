# Next.js移行ファイル

このディレクトリには、LaravelからNext.jsへの移行に必要なファイルが含まれています。

## ファイル構成

```
nextjs-migration/
├── README.md                      # このファイル
├── types.ts                       # TypeScript型定義
├── utils.ts                       # スコア計算・状態タイプ判定のユーティリティ
├── api.ts                         # API呼び出し関数
├── laravel-api-example.php        # Laravel APIエンドポイントのサンプル
├── components/
│   ├── DiagnosisForm.tsx          # 満足度診断フォーム
│   ├── ImportanceForm.tsx        # 重要度診断フォーム
│   ├── ResultView.tsx             # 診断結果表示
│   └── RadarChart.tsx             # レーダーチャート
└── pages/
    ├── start.tsx                  # 診断開始ページ
    ├── importance.tsx             # 重要度診断ページ
    └── result.tsx                 # 診断結果ページ
```

## セットアップ手順

### 1. Next.jsプロジェクトのセットアップ

```bash
# Next.jsプロジェクトを作成（まだの場合）
npx create-next-app@latest carefle-frontend --typescript --tailwind --app

# 必要なパッケージをインストール
cd carefle-frontend
npm install chart.js
npm install @types/chart.js
```

### 2. ファイルのコピー

1. `types.ts`, `utils.ts`, `api.ts` を `lib/career-satisfaction/` にコピー
2. `components/` 内のファイルを `components/career-satisfaction/` にコピー
3. `pages/` 内のファイルを `app/career-satisfaction-diagnosis/` にコピー（App Routerの場合）

### 3. Laravel APIエンドポイントの実装

`laravel-api-example.php` を参考に、`routes/api.php` にAPIエンドポイントを追加してください。

または、既存のコントローラーにAPIメソッドを追加：
- `CareerSatisfactionDiagnosisController::resultApi()` は既に追加済み

### 4. 環境変数の設定

`.env.local` に以下を追加：

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### 5. 認証の設定

Laravel Sanctumを使用している場合、CSRFトークンとセッション認証を設定してください。

## 使用方法

### 診断開始

```
/career-satisfaction-diagnosis/start
```

### 重要度診断

```
/career-satisfaction-diagnosis/importance/[id]
```

### 診断結果

```
/career-satisfaction-diagnosis/result/[id]
```

## 注意事項

1. **認証**: すべてのAPIエンドポイントは認証が必要です
2. **CORS**: Laravel側でCORS設定が必要な場合があります
3. **セッション**: セッション認証を使用する場合、`credentials: 'include'` を設定済みです
4. **エラーハンドリング**: 適切なエラーハンドリングを追加してください
5. **ローディング状態**: 必要に応じてローディングスピナーを追加してください

## スタイリング

既存のTailwind CSSクラスを使用しています：
- `card-refined`: カードスタイル
- `heading-1`, `heading-2`, `heading-3`: 見出し
- `body-text`, `body-small`, `body-large`: 本文
- `btn-primary`, `btn-secondary`: ボタン

カスタムクラスが定義されていない場合は、適切なスタイルを追加してください。

## テスト

各コンポーネントとページをテストしてください：
- 満足度診断フォームの動作確認
- 重要度診断フォームの動作確認
- 診断結果の表示確認
- レーダーチャートの表示確認
- API呼び出しのエラーハンドリング

## トラブルシューティング

### API呼び出しが失敗する

- CORS設定を確認
- 認証トークンが正しく送信されているか確認
- Laravel側のログを確認

### レーダーチャートが表示されない

- Chart.jsが正しくインストールされているか確認
- ブラウザのコンソールでエラーを確認

### スタイルが適用されない

- Tailwind CSSの設定を確認
- カスタムクラスが定義されているか確認

# Next.js 移管時の指示テンプレート（Cursor 向け）

職業満足度診断を Laravel から Next.js に移管するとき、**Next.js 側の作業者（または Cursor）にそのまま渡せる指示**をまとめたものです。  
「こう言えばうまくいく」形で、段階ごとにコピー＆ペーストして使えます。

---

## 前提として一度渡す情報

Next.js プロジェクトで作業する人（または Cursor）に、最初に次のように伝えておくとスムーズです。

```
【前提】
- このプロジェクトには Laravel バックエンドがあり、職業満足度診断の API を提供する（またはこれから実装する）。
- Next.js はその API を叩いて「開始 → 満足度フォーム → 重要度フォーム → 結果」の一連フローを実装する。
- 仕様・型・API 設計は次のリポジトリ／フォルダを参照する：
  - Laravel プロジェクト: （この carefle のパス、または URL）
  - 特に次のファイルを仕様の正として扱う：
    - NEXTJS_MIGRATION_GUIDE.md … API 一覧・データ構造・ビジネスロジック
    - nextjs-migration/ … 型(types.ts)、API 呼び出し(api.ts)、コンポーネント・ページのたたき台
    - resources/views/career-satisfaction-diagnosis/result.blade.php … 結果画面の文言・構成の正
```

---

## 指示の出し方（順番に使う）

### 1. プロジェクト構成と「仕様の正」の指定

```
職業満足度診断を Next.js（App Router）で実装します。

- ルート構成は以下とする：
  - /career-satisfaction-diagnosis/start … 診断開始＋満足度フォーム
  - /career-satisfaction-diagnosis/importance/[id] … 重要度フォーム
  - /career-satisfaction-diagnosis/result/[id] … 診断結果

- 型・API・UI の「仕様の正」は、同一リポジトリ内の nextjs-migration/ および NEXTJS_MIGRATION_GUIDE.md とする。
- nextjs-migration/ の types.ts, api.ts, components/*, pages/* を、App Router 用に次のように配置し直す：
  - lib/career-satisfaction/ に types.ts, utils.ts, api.ts
  - components/career-satisfaction/ に DiagnosisForm, ImportanceForm, ResultView, RadarChart
  - app/career-satisfaction-diagnosis/start/page.tsx, importance/[id]/page.tsx, result/[id]/page.tsx
- インポートパスは新しい配置に合わせてすべて修正すること。
```

---

### 2. API ベースURLと環境変数

```
職業満足度診断の API 呼び出しは、環境変数 NEXT_PUBLIC_API_URL をベースに行う。

- api.ts（または lib/career-satisfaction/api.ts）では次とする：
  - const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
- .env.local に NEXT_PUBLIC_API_URL を追加する旨を README またはセットアップ手順に書く。
```

---

### 3. 結果画面の文言・構成を Laravel と揃える

```
診断結果画面（ResultView）の文言・構成は、Laravel の正と同じにする。

- 参照する正：このリポジトリの resources/views/career-satisfaction-diagnosis/result.blade.php
- 次を Laravel 版と一致させる：
  - ファーストビュー「いまは、こんな距離感にいます」のパターン文言（PATTERN_1〜PATTERN_DEFAULT）
  - 状態サマリー「今の状態をひとことで言うと」の箇条書き（SUMMARY_*）
  - 領域別「引っかかり」のメッセージ（people/profession/progress/purpose/privilege × mild/moderate/severe）
  - 安心ゾーンのメッセージ（各 pillar）
  - 横棒「今の仕事を『続けること』への気持ち」のラベル（前向き〜迷いがある）
  - 「次の一歩」の4つ＋「診断を終えてホームへ」「もう一度診断する」「誰かに話して整理する（面談）」の有無・文言
  - メモは「あなたのメモ」として該当領域にそのまま引用表示（AIは使わない）
- API の診断結果レスポンスに relationshipPattern, summaryPattern, continuationPosition, stuckPointDetails[].memos, safeZoneDetails[].memos が含まれる前提で表示する。
```

---

### 4. 画面フローと API の対応関係

```
次のユーザー操作と API の対応を守ること。

1) 開始ページ（/career-satisfaction-diagnosis/start）
   - 表示時：GET /api/questions?type=work と GET /api/career-satisfaction-diagnosis/start を呼ぶ。
   - start のレスポンスから diagnosisId と answers を使い、questions は questions API から使う。

2) 満足度フォーム（同じ開始ページ内）
   - 回答保存: POST /api/career-satisfaction-diagnosis/{id}/answers（question_id, answer_value, comment）
   - 完了クリック時: POST /api/career-satisfaction-diagnosis/{id}/finish 後に /career-satisfaction-diagnosis/importance/[id] へ遷移。

3) 重要度フォーム（/career-satisfaction-diagnosis/importance/[id]）
   - 重要度保存: POST /api/career-satisfaction-diagnosis/{id}/importance-answers
   - 完了クリック時: POST /api/career-satisfaction-diagnosis/{id}/finish-importance 後に /career-satisfaction-diagnosis/result/[id] へ遷移。

4) 結果（/career-satisfaction-diagnosis/result/[id]）
   - 表示時：GET /api/career-satisfaction-diagnosis/{id}/result で取得し、ResultView に渡す。
```

---

### 5. 認証・CORS の前提を伝える

```
職業満足度診断の API は認証必須とする。

- すべてのリクエストで credentials: 'include' を使う（api.ts で fetch に設定済みであること）。
- Laravel が別オリジンのときは CORS で当該 Next のオリジンと credentials: true を許可する想定。
- 401 のときはログイン画面またはトップへリダイレクトするなど、認証エラー処理を実装すること。
```

---

### 6. スタイルとレイアウトの指定

```
職業満足度診断の画面は、NEXTJS_MIGRATION_GUIDE および nextjs-migration で使っている Tailwind クラスに揃える。

- カード: card-refined（なければ同様の角丸・枠・背景を定義）
- 見出し: heading-1, heading-2, heading-3
- 本文: body-text, body-small, body-large
- ボタン: btn-primary, btn-secondary
- 背景色は #F0F7FF / #EAF3FF、アクセントは #6BB6FF, #2E5C8A, #1E3A5F をベースとする。
- 既存のデザインシステムがある場合は、それに合わせて上記をマッピングすること。
```

---

### 7. リンク・導線の指定

```
結果画面のリンクは次のようにする。

- 「診断を終えてホームへ」→ /dashboard（またはホームのパス）
- 「もう一度診断する」→ /career-satisfaction-diagnosis/start
- 状態タイプ B のとき「誰かに話して整理する（面談）」を表示し、クリック先は仮で # または将来の面談予約 URL。
- 「この結果を管理者に共有する」は、Laravel の share-preview を使う場合はフルURL（例: ${API_ORIGIN}/share-preview/career-satisfaction/${id}）とする。同一アプリ内に組み込む場合は /share-preview/career-satisfaction/[id] などルートを決める。
```

---

### 8. エラー・ローディング

```
以下を必須とする。

- すべての API 呼び出しで try/catch し、失敗時はユーザーにメッセージを表示する。
- 診断開始・質問取得・結果取得の読み込み中は「読み込み中...」などのローディング表示をする。
- 401 のときは認証エラーとして扱い、ログインやホームへ誘導する。
```

---

### 9. 一括で渡すときの「オールインワン」指示例

Cursor に一度に渡したいときは、次のようにまとめて指示できます。

```
【職業満足度診断の Next.js 実装】

このリポジトリの「職業満足度診断」を Next.js（App Router）で実装する。

■ 参照する仕様
- NEXTJS_MIGRATION_GUIDE.md（API・データ構造）
- nextjs-migration/（型・api・components・pages のたたき台）
- resources/views/career-satisfaction-diagnosis/result.blade.php（結果画面の文言の正）

■ やること
1) App Router で次のルートを作る：
   - app/career-satisfaction-diagnosis/start/page.tsx（開始＋満足度フォーム）
   - app/career-satisfaction-diagnosis/importance/[id]/page.tsx
   - app/career-satisfaction-diagnosis/result/[id]/page.tsx
2) nextjs-migration の types, utils, api, components を lib/career-satisfaction と components/career-satisfaction に配置し、インポートを全て修正する。
3) 結果画面の文言・構成（距離感・サマリー・引っかかり・安心ゾーン・メモ引用・横棒・次の一歩）を result.blade.php と一致させる。
4) 画面フローは「開始 → 満足度完了 → importance/[id] → 重要度完了 → result/[id]」とし、各段階で該当 API を呼ぶ（仕様は NEXTJS_MIGRATION_GUIDE の API 設計に従う）。
5) API ベースURLは NEXT_PUBLIC_API_URL、全リクエストで credentials: 'include' とする。
6) ローディング・エラー・401 時の誘導を実装する。
7) スタイルは NEXTJS_MIGRATION_GUIDE の Tailwind クラスと色に合わせる。
```

---

## うまくいくポイント

1. **「正」をはっきり指定する**  
   「NEXTJS_MIGRATION_GUIDE.md と nextjs-migration/ と result.blade.php が正」と一言書くと、Cursor が迷いにくい。

2. **ルートと API の対応を書く**  
   「開始ページでは questions と start を叩く」「完了時に finish を叩いてから importance/[id] へ遷移」のように、画面単位で対応関係を書く。

3. **文言は「Laravel と一致」とだけ言う**  
   「result.blade.php と一致させる」と書けば、パターン文やメモ表示の仕様を長く書かなくてよい。

4. **段階を分けて指示する**  
   いきなり全部やらせず、「まずルートと配置」「次に結果の文言」「最後に認証・エラー」のように分けると失敗が少ない。

5. **環境変数と CORS を最初に伝える**  
   NEXT_PUBLIC_API_URL と credentials: 'include'、および「別オリジンなら CORS を Laravel 側で許可する前提」を最初に書いておく。

---

## 困ったときの切り分け

- **API が動かない**  
  → 「Laravel 側の API 実装は別タスク。Next では NEXT_PUBLIC_API_URL を正しく設定し、レスポンスの型を NEXTJS_MIGRATION_GUIDE の DiagnosisResult に合わせて表示する」と指示する。

- **結果の表示が Laravel と違う**  
  → 「resources/views/career-satisfaction-diagnosis/result.blade.php の該当ブロックと、ResultView の該当部分を diff のように比較し、文言・順序・メモの出し方を一致させる」と指示する。

- **どのファイルを触るか分からない**  
  → 「nextjs-migration/ のどのファイルが、App 構成では lib/ や app/ のどこに対応するか一覧にし、その対応表のとおりに配置とインポートを直す」と指示する。

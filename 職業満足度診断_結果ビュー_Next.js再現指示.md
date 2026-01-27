# 職業満足度診断・結果ビュー — Next.js 再現指示

Laravel の `resources/views/career-satisfaction-diagnosis/result.blade.php` のビューと表現を **そのまま** Next.js 側で再現するための指示です。レイアウト・文言・クラス・データの対応を揃えてください。

---

## 1. 前提・正とするファイル

- **唯一の正**: このリポジトリの `resources/views/career-satisfaction-diagnosis/result.blade.php`
- **目標**: 上記の DOM 構造・Tailwind クラス・文言・表示順を、Next.js の結果画面（ResultView 等）で **徹底的に同じ** にすること。

---

## 2. ルートコンテナと全体レイアウト

結果エリアの最外层は次の要素とする。

- **ルート div**
  - クラス: `min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm`
  - 中身は `w-full max-w-6xl mx-auto space-y-10` のラッパーで、各セクションを縦に並べる。
- **背景色**: `#EAF3FF`（画面全体）
- **幅**: 最大 `max-w-6xl`、中央寄せ

`content-padding` と `section-spacing-sm` は、Laravel 側で `p-6 lg:p-8` や `space-y-*` で表現している部分に相当する。Next 側で同じ余白・間隔が出るようにする。

---

## 3. セクション構成と表示順（この順で必ず並べる）

1. ファーストビュー（距離感＋横棒図解）
2. 状態サマリー「今の状態をひとことで言うと」
3. 領域別「今、気持ちが揺れやすいポイント」（引っかかりありの場合のみ）
4. 「今、比較的安定しているところ」（安心ゾーン、1件以上ある場合のみ）
5. 全体バランスの可視化（レーダーチャート）
6. 「今の距離感にいる人が、よく選ぶ行動」＋CTA ボタン
7. 「この結果を管理者に共有する」リンク（管理者に共有していない場合のみ）

---

## 4. 各セクションの DOM・文言・クラス

### 4.1 ファーストビュー「いまは、こんな距離感にいます」

**外側カード**
- クラス: `card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff] relative overflow-hidden`

**右上バッジ**（`diagnosis.is_admin_visible === true` のときだけ表示）
- クラス: `absolute top-0 right-0 p-4`
- 子要素: `text-xs px-3 py-1 rounded-full bg-green-50 border border-green-300 text-green-700 font-medium`
- 文言: `管理者に共有中`

**見出しブロック**
- 小ラベル:  
  - 文言: `いまは、こんな距離感にいます`  
  - クラス: `body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2`
- メイン見出し:  
  - 文言: API の `relationshipPattern` に対応する「main」（下のパターン表を参照）  
  - クラス: `heading-2 text-3xl md:text-4xl mb-4 text-[#1E3A5F]`
- サブ文:  
  - 文言: 同上「sub」  
  - クラス: `body-large text-[#2E5C8A] leading-relaxed max-w-2xl`

**距離感パターン（relationshipPattern）と文言**

| キー | main | sub |
|-----|------|-----|
| PATTERN_1 | 全体的にバランスが取れている距離感 | 今の仕事を続けることに大きな不安はなく、落ち着いてこれからのことを考えられる状態です。 |
| PATTERN_2 | 一部に軽微なギャップがあるものの、全体的には安定している距離感 | 今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。 |
| PATTERN_3 | 特定の領域で理想とのギャップを感じている距離感 | 今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。 |
| PATTERN_4 | 複数の領域で軽微なギャップを感じている距離感 | 今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。 |
| PATTERN_5 | 特定の領域で中程度のギャップを感じている距離感 | 今の仕事を続けることに迷いが生まれやすく、一度立ち止まって整理したい状態です。 |
| PATTERN_6 | 複数の領域で中程度のギャップを感じている距離感 | 今の仕事を続けることに迷いが生まれやすく、一度立ち止まって整理したい状態です。 |
| PATTERN_7 | 深刻なギャップを感じている距離感 | 今の仕事を続けることに大きな迷いがあり、一度立ち止まって整理することが大切な状態です。 |
| PATTERN_8 | 複数の領域でギャップがあり、満足度も低い距離感 | 今の仕事を続けることに大きな迷いがあり、一度立ち止まって整理することが大切な状態です。 |
| PATTERN_DEFAULT | 今の仕事との距離感を見つめ直している状態 | 診断結果から、今の仕事との関わり方について一度立ち止まって考えてみる時期に来ているようです。 |

---

### 4.2 横棒「今の仕事を『続けること』への気持ち」

ファーストビュー内、上記の下に同じカード内で表示する。

- **区切り**: `mt-12 py-8 border-t border-[#6BB6FF]/20`
- **キャプション**:  
  - 文言: `今の仕事を「続けること」への気持ち`  
  - クラス: `body-small text-[#4B7BB5] mb-6 text-center font-semibold`
- **棒のラベル**:  
  - 左: `前向き` … クラス `absolute -top-6 left-0 body-small text-[#4B7BB5]`  
  - 右: `迷いがある` … クラス `absolute -top-6 right-0 body-small text-[#FF9E6B]`
- **背景の棒**:  
  - クラス: `absolute w-full h-1.5 bg-gradient-to-r from-[#6BB6FF] via-[#cbd5e1] to-[#FF9E6B] rounded-full`
- **現在地ピン**:  
  - 位置: `left: ${continuationPosition}%`（API の `continuationPosition`、0–100）
  - 円: `w-4 h-4 bg-[#1E3A5F] rounded-full border-2 border-white shadow-md`
  - 吹き出し文言: `今のあなた: ${posText}`  
  - 吹き出しクラス: `absolute top-6 whitespace-nowrap bg-[#1E3A5F] text-white text-[10px] px-2 py-1 rounded`

**posText の決め方**（`continuationPosition` の値で分岐）

| 条件 | 表示文言 |
|------|----------|
| pos >= 80 | 前向きに続けられる気持ち |
| pos >= 60 | 続けられる気持ちがある |
| pos >= 40 | 続けることに迷いがある |
| pos >= 20 | 続けることに大きな迷いがある |
| 上以外 | 続けることに強い迷いがある |

---

### 4.3 状態サマリー「今の状態をひとことで言うと」

- **外側**: `card-refined p-8 bg-white border border-[#6BB6FF]/10`
- **見出し**:  
  - 文言: `📍 今の状態をひとことで言うと`（📍 は絵文字のまま）  
  - クラス: `heading-3 text-xl mb-6 text-[#1E3A5F] flex items-center gap-2`、絵文字は `text-2xl`
- **内側ボックス**: `bg-[#F0F7FF] rounded-2xl p-8 border border-[#6BB6FF]/20`
- **リスト**: `ul.space-y-4`、各 `li` は `flex items-start gap-3 text-lg text-[#1E3A5F]`
  - 先頭: `•`（クラス `text-[#6BB6FF] mt-1`）
  - 2番目: テキスト。内容は API の `summaryPattern` に対応する配列（下の表）をその順で表示。

**summaryPattern と箇条書き**

| キー | 表示する3行（配列の順） |
|------|--------------------------|
| SUMMARY_C_HIGH | 大きな不満があるわけではない / 納得感が保たれている / 今の状態を維持できる |
| SUMMARY_C_MID | 大きな不満があるわけではない / 納得感が少しずつ薄れている / 気持ちの置きどころを探している段階 |
| SUMMARY_A_HIGH | 大きな不満があるわけではない / ただし、納得感が少しずつ薄れている / 気持ちの置きどころを探している段階 |
| SUMMARY_A_MID | 一部の領域で不満を感じている / 納得感が薄れている / 気持ちの置きどころを探している段階 |
| SUMMARY_B_MID | 複数の領域で不満を感じている / 納得感が薄れている / 気持ちの置きどころを探している段階 |
| SUMMARY_B_LOW | 複数の領域で大きな不満を感じている / 納得感が大きく薄れている / 一度立ち止まって整理したい段階 |
| SUMMARY_DEFAULT | 今の状態を客観的に見つめ直す段階 / 納得感の源泉を確認する必要がある / 無理をせず、自分のペースで考える |

---

### 4.4 今、気持ちが揺れやすいポイント（stuckPointDetails）

- **表示条件**: `stuckPointCount > 0` のときだけブロック全体を表示。
- **見出し**:  
  - 文言: `⚡️ 今、気持ちが揺れやすいポイント`  
  - クラス: `heading-3 text-xl text-[#1E3A5F] flex items-center gap-2 px-2`、絵文字は `text-2xl`
- **グリッド**: `grid grid-cols-1 md:grid-cols-2 gap-6`
- **各カード**:
  - `card-refined p-6 bg-white border-l-4` ＋  severity でボーダー色を変える:
    - `diff < -20` → `border-orange-400`（severe）
    - `diff < -10` → `border-orange-300`（moderate）
    - それ以外 → `border-blue-300`（mild）
  - タイトル: `detail.label`、右に `span.text-xs px-2 py-1 rounded bg-orange-50 text-orange-700` で「ギャップあり」
  - 本文: pillar と severity で下の `stuckPointMessages` から 1 文を選んで表示。
  - **あなたのメモ**: `detail.memos` が空でなければ、  
    - ラベル「あなたのメモ」＋各 `memo` を「「${memo}」」の形で表示。  
    - ラベルは `absolute -top-2.5 left-4 px-2 bg-[#F8FAFC] text-[10px] text-[#94A3B8] font-bold tracking-wider`。  
    - メモエリア: `mt-4 p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] relative`、本文は `body-small italic text-[#64748B]`。

**stuckPointMessages（pillar × severity → 1文）**

| pillar | mild | moderate | severe |
|--------|------|----------|--------|
| people | 日々の仕事に支障が出るほどではありませんが、「この人たちと長く一緒に働きたいか」と考えると、少し迷いが生まれやすい状態です。 | 周囲との関係性において、自分らしさを出しにくい感覚や、価値観のズレを見過ごせなくなってきているようです。 | 人間関係におけるストレスや違和感が大きく、今の環境で自分を保ち続けることに限界を感じ始めている可能性があります。 |
| profession | 役割や期待は理解しているものの、自分が本当にやりたいこととの間にわずかなズレを感じやすくなっています。 | 仕事の内容が自分の強みや価値観と合っていない感覚が強く、このまま続けていくことに疑問を感じている状態です。 | 仕事の意義や自分の適性に対して強い乖離を感じており、キャリアの方向性を根本から見直したい時期かもしれません。 |
| progress | 業務はこなせている一方で、「この期間で何が積み上がったか」を明確に言葉にしにくい感覚があります。 | 自身の成長が停滞している感覚があり、今の環境で得られる学びに限界を感じ始めているようです。 | 今の仕事が自分の将来に繋がっている実感が乏しく、時間を浪費しているような強い焦燥感があるかもしれません。 |
| purpose | 仕事の意味や目的は理解しているものの、自分にとっての意義を再確認したい気持ちが生まれやすい状態です。 | 組織の目指す方向と自分の想いが重なりにくくなっており、仕事への情熱を維持しにくい感覚があります。 | 会社のビジョンや目的に対して強い違和感があり、今の場所で働く理由を見失いつつある状態です。 |
| privilege | 環境や待遇面では大きな不満はないものの、長期的な視点で考えると少し不安を感じやすい状態です。 | 給与や労働時間、評価などの条件面において、自分の貢献に見合っていないという不満が強まっているようです。 | 生活リズムの乱れや待遇への強い不満があり、今の環境を維持することが心身の負担になっている可能性があります。 |

---

### 4.5 今、比較的安定しているところ（safeZoneDetails）

- **表示条件**: `Object.keys(safeZoneDetails).length > 0` のときだけ表示。
- **見出し**:  
  - 文言: `🌱 今、比較的安定しているところ`  
  - クラス: `heading-3 text-xl text-[#1E3A5F] flex items-center gap-2 px-2`、絵文字は `text-2xl`
- **グリッド**: `grid grid-cols-1 md:grid-cols-2 gap-6`
- **各カード**: `card-refined p-6 bg-green-50/30 border-l-4 border-green-300`
  - タイトル: `detail.label`
  - 本文: pillar に対応する下の `safeZoneMessages` の 1 文。
  - **あなたのメモ**: `detail.memos` が空でなければ表示。  
    - ラベル「あなたのメモ」は `absolute -top-2.5 left-4 px-2 bg-green-50/30 text-[10px] text-[#86B88F] font-bold tracking-wider`。  
    - エリア: `mt-4 p-4 bg-white/60 rounded-xl border border-green-100 relative`、本文は `body-small italic text-[#86B88F]`、各メモは「「${memo}」」。

**safeZoneMessages（pillar → 1文）**

| pillar | 文言 |
|--------|------|
| people | 信頼できる仲間に恵まれており、心理的な安全性が保たれていることが、あなたにとって大きな支えになっています。 |
| profession | 自分の強みを活かせる役割を担えており、仕事そのものに対する納得感や手応えを感じられています。 |
| progress | 日々の業務を通じて自身の成長を実感できており、キャリアの積み上げに対する安心感があります。 |
| purpose | 組織の目的と自分の価値観が一致しており、仕事を通じて社会に貢献している実感が持てています。 |
| privilege | 働く環境や条件面での満足度が高く、落ち着いてこれからのことを考えられる安定した土台があります。 |

---

### 4.6 全体バランスの可視化（レーダーチャート）

- **外側**: `card-refined p-8 bg-white border border-[#6BB6FF]/10`
- **見出し**: 文言 `全体バランスの可視化`、クラス `heading-3 text-xl mb-4 text-[#1E3A5F] text-center`
- **チャート領域**: `max-w-md mx-auto`。Laravel は canvas + Chart.js。Next では React 用の Chart.js / recharts 等で、`radarLabels`・`radarWorkData`（満足度）・`importanceDataset`（重要度）を同じ見た目で描く。

---

### 4.7 次の一歩と CTA

- **外側**: `card-refined p-8 bg-gradient-to-br from-[#1E3A5F] to-[#2E5C8A] text-white`
- **見出し**: `🚀 今の距離感にいる人が、よく選ぶ行動`
- **4つのアクション**（`grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8`、各 `bg-white/10 p-4 rounded-xl border border-white/20`）:
  1. 今の仕事で「何が引っかかっているか」を整理する
  2. 他の働き方や選択肢の話を聞いてみる
  3. あえて何も決めず、少し様子を見る
  4. 信頼できる人に考えを話してみる
- **ボタン**（`flex flex-col sm:flex-row justify-center gap-4`）:
  - 「診断を終えてホームへ」: `px-8 py-3 rounded-full bg-white text-[#1E3A5F] font-bold ...` → ホームへ
  - 「もう一度診断する」: `px-8 py-3 rounded-full bg-transparent border-2 border-white text-white ...` → 診断開始へ
  - 「誰かに話して整理する（面談）」: `stateType === 'B'` のときだけ表示、`bg-orange-400` 等 → 面談用 URL（仮なら #）

---

### 4.8 管理者共有リンク

- **表示条件**: `!diagnosis.is_admin_visible`
- **文言**: `この結果を管理者に共有する`
- **クラス**: `body-small text-[#4B7BB5] underline underline-offset-4 decoration-dotted`
- **配置**: `text-center pb-10`

---

## 5. API から渡すデータと表示との対応

ResultView には少なくとも次の props（または API レスポンスの展開）が必要です。

- `diagnosis`（`id`, `is_admin_visible` を使用）
- `relationshipPattern`（上記 PATTERN_* のキー）
- `summaryPattern`（上記 SUMMARY_* のキー）
- `continuationPosition`（0–100 の数値）
- `stuckPointCount`, `stuckPointDetails`（各要素に `label`, `diff`, `memos`）
- `safeZoneDetails`（各要素に `label`, `memos`）
- `stateType`（'A'|'B'|'C'、B のときだけ面談 CTA 表示）
- `radarLabels`, `radarWorkData`, `importanceDataset`（レーダー用）

文言はすべてこの文書と `result.blade.php` に合わせ、**1文字たりとも変えない**ようにしてください。

---

## 6. 渡し方の例（Cursor 等に渡すとき）

Next.js 担当や Cursor に渡すときは、例えば次のように指定するとよいです。

```
職業満足度診断の結果画面を、Laravel の result.blade.php と完全に同じビュー・表現で再現してください。

正とするファイル: このリポジトリの resources/views/career-satisfaction-diagnosis/result.blade.php

再現の詳細仕様は、「職業満足度診断_結果ビュー_Next.js再現指示.md」に記載しています。次の点を守って実装してください。

- ルートは min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm。中は max-w-6xl mx-auto space-y-10。
- セクションの順序は「距離感・横棒 → 状態サマリー → 揺れやすいポイント → 安定しているところ → レーダー → 次の一歩 → 管理者共有リンク」。
- すべての文言（距離感パターン・サマリー・揺れポイント・安心ゾーン・次の一歩・ボタン）は、同指示ファイル内の表および result.blade.php と一致させる。
- 横棒の「今のあなた」のラベルは continuationPosition に基づき、指示ファイルの posText 表のとおりに表示する。
- 引っかかり・安心ゾーンの「あなたのメモ」は、API の memos 配列を「「${memo}」」の形でそのまま引用表示する。
```

以上を守れば、ユーザーが触れるビューと表現を Laravel と同一にできます。

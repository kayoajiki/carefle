# 現職満足度診断 Next.js移行ガイド

## 概要

このドキュメントは、Laravelで実装されている現職満足度診断機能をNext.jsに移行するためのガイドです。

## 機能一覧

### 1. 満足度診断フォーム
- **ファイル**: `app/Livewire/CareerSatisfactionDiagnosisForm.php`
- **ビュー**: `resources/views/livewire/career-satisfaction-diagnosis-form.blade.php`
- **機能**:
  - Workタイプの質問を順番に表示
  - 各質問に対して1-5のスケールで回答
  - 任意のコメント入力
  - 進捗バーの表示
  - 下書き保存機能
  - スコア計算（満足度スコア、pillar別スコア）

### 2. 重要度診断フォーム
- **ファイル**: `app/Livewire/CareerSatisfactionDiagnosisImportanceForm.php`
- **ビュー**: `resources/views/livewire/career-satisfaction-diagnosis-importance-form.blade.php`
- **機能**:
  - 満足度診断完了後、同じ質問に対して重要度を入力
  - 1-5のスケールで重要度を評価
  - 状態タイプ（A/B/C）の判定と保存

### 3. 診断結果表示
- **ファイル**: `app/Http/Controllers/CareerSatisfactionDiagnosisController.php`
- **ビュー**: `resources/views/career-satisfaction-diagnosis/result.blade.php`
- **機能**:
  - 満足度スコアの表示
  - レーダーチャート（満足度 vs 重要度）
  - 理想とギャップのある領域の表示
  - 状態タイプ（A/B/C）の判定結果と推奨アクション

## データ構造

### CareerSatisfactionDiagnosis
```typescript
interface CareerSatisfactionDiagnosis {
  id: number;
  user_id: number;
  work_score: number | null; // 0-100
  work_pillar_scores: {
    purpose?: number;
    profession?: number;
    people?: number;
    privilege?: number;
    progress?: number;
  } | null;
  state_type: 'A' | 'B' | 'C' | null;
  is_completed: boolean;
  is_draft: boolean;
  is_admin_visible: boolean;
  created_at: string;
  updated_at: string;
}
```

### CareerSatisfactionDiagnosisAnswer
```typescript
interface CareerSatisfactionDiagnosisAnswer {
  id: number;
  career_satisfaction_diagnosis_id: number;
  question_id: number;
  answer_value: number; // 1-5
  comment: string | null;
  created_at: string;
  updated_at: string;
}
```

### CareerSatisfactionDiagnosisImportanceAnswer
```typescript
interface CareerSatisfactionDiagnosisImportanceAnswer {
  id: number;
  career_satisfaction_diagnosis_id: number;
  question_id: number;
  importance_value: number; // 1-5
  created_at: string;
  updated_at: string;
}
```

### Question
```typescript
interface Question {
  id: number;
  type: 'work' | 'life';
  pillar: 'purpose' | 'profession' | 'people' | 'privilege' | 'progress';
  weight: number;
  text: string;
  helper: string | null;
  options: Array<{
    value: number; // 1-5
    label: string;
    desc: string;
  }>;
  order: number;
}
```

## API設計

### 1. 診断開始
```
GET /api/career-satisfaction-diagnosis/start
```
**レスポンス**:
- 既存の下書き診断がある場合: 診断IDと回答データを返す
- 新規の場合: 新しい診断を作成してIDを返す

### 2. 質問一覧取得
```
GET /api/questions?type=work
```
**レスポンス**: Question[] (order順)

### 3. 回答保存
```
POST /api/career-satisfaction-diagnosis/{id}/answers
Body: {
  question_id: number;
  answer_value: number;
  comment?: string;
}
```

### 4. 下書き保存
```
POST /api/career-satisfaction-diagnosis/{id}/save-draft
```

### 5. 満足度診断完了
```
POST /api/career-satisfaction-diagnosis/{id}/finish
```
**処理**:
- すべての質問に回答があるか確認
- スコア計算
- 診断を完了状態にする

### 6. 重要度回答保存
```
POST /api/career-satisfaction-diagnosis/{id}/importance-answers
Body: {
  question_id: number;
  importance_value: number;
}
```

### 7. 重要度診断完了
```
POST /api/career-satisfaction-diagnosis/{id}/finish-importance
```
**処理**:
- すべての質問に重要度が入力されているか確認
- 状態タイプを計算して保存

### 8. 診断結果取得
```
GET /api/career-satisfaction-diagnosis/{id}/result
```
**レスポンス**:
```typescript
{
  diagnosis: CareerSatisfactionDiagnosis;
  workScore: number;
  radarLabels: string[];
  radarWorkData: (number | null)[];
  importanceDataset: (number | null)[];
  workPillarScores: Record<string, number>;
  importanceWork: Record<string, number>;
  pillarLabels: Record<string, string>;
  stuckPoints: string[];
  stuckPointCount: number;
  stuckPointDetails: Record<string, {
    label: string;
    satisfaction: number;
    importance: number;
    diff: number;
  }>;
  maxDiff: number | null;
  gapSummary: {
    mild: string[];
    moderate: string[];
    severe: string[];
  };
  stateType: 'A' | 'B' | 'C' | null;
}
```

## ビジネスロジック

### スコア計算

#### 1. 満足度スコア計算
```typescript
// 1-5を0-100に変換
const scaledScore = ((answerValue - 1) / 4) * 100;

// pillar別スコア（weightで加重平均）
const pillarScore = sum(scaledScore * weight) / sum(weight);

// 全体スコア（各pillarの平均をweightで加重平均）
const workScore = sum(pillarAvg * pillarWeight) / sum(pillarWeight);

// 一つでもpillarのスコアが100点未満の場合、全体スコアが100点にならないようにする
if (minPillarScore < 100) {
  workScore = (workScore + minPillarScore) / 2;
}
```

#### 2. 重要度スコア計算
```typescript
// 1-5を0-100に変換
const importanceValue = ((importanceValue - 1) / 4) * 100;

// pillar別スコア（weightで加重平均）
const importancePillarScore = sum(importanceValue * weight) / sum(weight);
```

#### 3. 状態タイプ判定
```typescript
function determineStateType(
  workPillarScores: Record<string, number>,
  importanceWork: Record<string, number>,
  workScore: number
): 'A' | 'B' | 'C' {
  // 引っかかりポイントをカウント（満足度 < 重要度）
  const stuckPoints: string[] = [];
  let maxDiff: number | null = null;
  
  for (const [pillar, satisfactionScore] of Object.entries(workPillarScores)) {
    const importanceScore = importanceWork[pillar];
    if (importanceScore !== null && satisfactionScore !== null) {
      const diff = satisfactionScore - importanceScore;
      if (diff < 0) {
        stuckPoints.push(pillar);
        if (maxDiff === null || diff < maxDiff) {
          maxDiff = diff;
        }
      }
    }
  }
  
  const stuckPointCount = stuckPoints.length;
  
  // 状態タイプC（25%）: 今は動かない判断が妥当
  if (stuckPointCount === 0) {
    return 'C';
  }
  
  if (stuckPointCount >= 1 && stuckPointCount <= 2) {
    if (maxDiff >= -10 && workScore >= 70) {
      return 'C';
    }
  }
  
  // 状態タイプA（25%）: 一人で内省を続けられる
  if (stuckPointCount >= 1 && stuckPointCount <= 2) {
    if (maxDiff >= -10 && workScore < 70) {
      return 'A';
    }
  }
  
  if (stuckPointCount >= 3) {
    if (maxDiff >= -10 && workScore >= 70) {
      return 'A';
    }
  }
  
  // 状態タイプB（50%）: 一人だと堂々巡りになりやすい
  return 'B';
}
```

## Next.js実装の推奨構造

```
app/
  career-satisfaction-diagnosis/
    start/
      page.tsx                    # 診断開始ページ
    form/
      page.tsx                    # 満足度診断フォーム
    importance/
      [id]/
        page.tsx                  # 重要度診断フォーム
    result/
      [id]/
        page.tsx                  # 診断結果表示

lib/
  career-satisfaction/
    types.ts                      # 型定義
    utils.ts                      # スコア計算、状態タイプ判定
    api.ts                        # API呼び出し関数

components/
  career-satisfaction/
    DiagnosisForm.tsx             # 満足度診断フォームコンポーネント
    ImportanceForm.tsx            # 重要度診断フォームコンポーネント
    ResultView.tsx                # 診断結果表示コンポーネント
    RadarChart.tsx                # レーダーチャートコンポーネント
```

## スタイリング

既存のTailwind CSSクラスをそのまま使用可能:
- `card-refined`: カードスタイル
- `heading-1`, `heading-2`, `heading-3`: 見出し
- `body-text`, `body-small`, `body-large`: 本文
- `btn-primary`, `btn-secondary`: ボタン
- カラーパレット: `#F0F7FF`, `#6BB6FF`, `#2E5C8A`, `#1E3A5F`

## 移行チェックリスト

- [ ] APIエンドポイントの実装（Laravel側）
- [ ] Next.jsプロジェクトのセットアップ
- [ ] 型定義の作成
- [ ] スコア計算ロジックの実装
- [ ] 状態タイプ判定ロジックの実装
- [ ] 診断開始ページの実装
- [ ] 満足度診断フォームの実装
- [ ] 重要度診断フォームの実装
- [ ] 診断結果表示ページの実装
- [ ] レーダーチャートの実装（Chart.jsまたはrecharts）
- [ ] 下書き保存機能の実装
- [ ] エラーハンドリング
- [ ] ローディング状態の実装
- [ ] 認証・認可の実装
- [ ] テスト

// スコア計算と状態タイプ判定のユーティリティ関数

import { Question, CareerSatisfactionDiagnosisAnswer, CareerSatisfactionDiagnosisImportanceAnswer } from './types';

/**
 * 1-5のスケールを0-100に変換
 */
export function scaleTo100(value: number): number {
  return ((value - 1) / 4) * 100;
}

/**
 * 満足度スコアを計算
 */
export function calculateSatisfactionScores(
  answers: CareerSatisfactionDiagnosisAnswer[],
  questions: Question[]
): {
  workScore: number;
  workPillarScores: Record<string, number>;
} {
  const workScores: number[] = [];
  const workPillarScores: Record<string, number[]> = {};

  // 各回答をスケール変換して集計
  answers.forEach((answer) => {
    const question = questions.find((q) => q.id === answer.question_id);
    if (!question || question.type !== 'work') return;

    const scaledScore = scaleTo100(answer.answer_value);
    workScores.push(scaledScore);

    // pillar別に集計
    if (!workPillarScores[question.pillar]) {
      workPillarScores[question.pillar] = [];
    }
    workPillarScores[question.pillar].push(scaledScore);
  });

  // pillar別スコアを計算（weightで加重平均）
  const workPillarFinal: Record<string, number> = {};
  Object.keys(workPillarScores).forEach((pillar) => {
    const pillarQuestions = questions.filter(
      (q) => q.type === 'work' && q.pillar === pillar
    );

    let pillarScore = 0;
    let pillarWeight = 0;

    pillarQuestions.forEach((q) => {
      const answer = answers.find((a) => a.question_id === q.id);
      if (answer && q.weight) {
        const scaledScore = scaleTo100(answer.answer_value);
        pillarScore += scaledScore * q.weight;
        pillarWeight += q.weight;
      }
    });

    if (pillarWeight > 0) {
      workPillarFinal[pillar] = Math.round(pillarScore / pillarWeight);
    } else {
      const scores = workPillarScores[pillar];
      workPillarFinal[pillar] = Math.round(
        scores.reduce((sum, s) => sum + s, 0) / scores.length
      );
    }
  });

  // Workスコア：各pillarの平均をweightで加重平均
  let workScore = 0;
  let totalWeight = 0;

  Object.keys(workPillarFinal).forEach((pillar) => {
    const pillarWeight = questions
      .filter((q) => q.type === 'work' && q.pillar === pillar)
      .reduce((sum, q) => sum + q.weight, 0);

    if (pillarWeight > 0) {
      workScore += workPillarFinal[pillar] * pillarWeight;
      totalWeight += pillarWeight;
    }
  });

  if (totalWeight > 0) {
    workScore = Math.round(workScore / totalWeight);
  } else {
    workScore = 0;
  }

  // 一つでもpillarのスコアが100点未満の場合、全体スコアが100点にならないようにする
  const pillarScoreValues = Object.values(workPillarFinal);
  const minPillarScore = pillarScoreValues.length > 0 ? Math.min(...pillarScoreValues) : 100;
  if (minPillarScore < 100) {
    workScore = Math.round((workScore + minPillarScore) / 2);
  }

  return {
    workScore,
    workPillarScores: workPillarFinal,
  };
}

/**
 * 重要度スコアを計算
 */
export function calculateImportanceScores(
  importanceAnswers: CareerSatisfactionDiagnosisImportanceAnswer[],
  questions: Question[]
): Record<string, number> {
  const importanceWork: Record<string, number> = {};

  // 質問をpillarでグループ化
  const questionsByPillar: Record<string, Question[]> = {};
  questions
    .filter((q) => q.type === 'work')
    .forEach((q) => {
      if (!questionsByPillar[q.pillar]) {
        questionsByPillar[q.pillar] = [];
      }
      questionsByPillar[q.pillar].push(q);
    });

  // 各pillarの重要度スコアを計算
  Object.keys(questionsByPillar).forEach((pillar) => {
    let pillarScore = 0;
    let pillarWeight = 0;

    questionsByPillar[pillar].forEach((q) => {
      const answer = importanceAnswers.find((a) => a.question_id === q.id);
      if (answer && q.weight) {
        const importanceValue = scaleTo100(answer.importance_value);
        pillarScore += importanceValue * q.weight;
        pillarWeight += q.weight;
      }
    });

    if (pillarWeight > 0) {
      importanceWork[pillar] = Math.round(pillarScore / pillarWeight);
    }
  });

  return importanceWork;
}

/**
 * 状態タイプを判定
 */
export function determineStateType(
  workPillarScores: Record<string, number>,
  importanceWork: Record<string, number>,
  workScore: number
): 'A' | 'B' | 'C' {
  // 引っかかりポイントをカウント（満足度 < 重要度）
  const stuckPoints: string[] = [];
  let maxDiff: number | null = null;

  Object.entries(workPillarScores).forEach(([pillar, satisfactionScore]) => {
    const importanceScore = importanceWork[pillar];
    if (importanceScore !== null && importanceScore !== undefined && satisfactionScore !== null && satisfactionScore !== undefined) {
      const diff = satisfactionScore - importanceScore;
      if (diff < 0) {
        stuckPoints.push(pillar);
        if (maxDiff === null || diff < maxDiff) {
          maxDiff = diff;
        }
      }
    }
  });

  const stuckPointCount = stuckPoints.length;

  // 状態タイプC（25%）: 今は動かない判断が妥当
  if (stuckPointCount === 0) {
    return 'C';
  }

  if (stuckPointCount >= 1 && stuckPointCount <= 2) {
    if (maxDiff !== null && maxDiff >= -10 && workScore >= 70) {
      return 'C';
    }
  }

  // 状態タイプA（25%）: 一人で内省を続けられる
  if (stuckPointCount >= 1 && stuckPointCount <= 2) {
    if (maxDiff !== null && maxDiff >= -10 && workScore < 70) {
      return 'A';
    }
  }

  if (stuckPointCount >= 3) {
    if (maxDiff !== null && maxDiff >= -10 && workScore >= 70) {
      return 'A';
    }
  }

  // 状態タイプB（50%）: 一人だと堂々巡りになりやすい
  return 'B';
}

/**
 * ギャップサマリーを計算
 */
export function calculateGapSummary(
  stuckPointDetails: Record<string, {
    label: string;
    satisfaction: number;
    importance: number;
    diff: number;
  }>
): {
  mild: string[];
  moderate: string[];
  severe: string[];
} {
  const gapSummary = {
    mild: [] as string[],
    moderate: [] as string[],
    severe: [] as string[],
  };

  Object.values(stuckPointDetails).forEach((detail) => {
    if (detail.diff >= -10) {
      gapSummary.mild.push(detail.label);
    } else if (detail.diff >= -20) {
      gapSummary.moderate.push(detail.label);
    } else {
      gapSummary.severe.push(detail.label);
    }
  });

  return gapSummary;
}

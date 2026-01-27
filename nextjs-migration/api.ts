// API呼び出し関数

import {
  CareerSatisfactionDiagnosis,
  Question,
  CareerSatisfactionDiagnosisAnswer,
  CareerSatisfactionDiagnosisImportanceAnswer,
  DiagnosisResult,
  DiagnosisFormState,
} from './types';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

/**
 * 診断開始（既存の下書きを取得または新規作成）
 */
export async function startDiagnosis(): Promise<DiagnosisFormState> {
  const response = await fetch(`${API_BASE_URL}/career-satisfaction-diagnosis/start`, {
    method: 'GET',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('診断の開始に失敗しました');
  }

  return response.json();
}

/**
 * 質問一覧を取得
 */
export async function getQuestions(type: 'work' | 'life' = 'work'): Promise<Question[]> {
  const response = await fetch(`${API_BASE_URL}/questions?type=${type}`, {
    method: 'GET',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('質問の取得に失敗しました');
  }

  return response.json();
}

/**
 * 回答を保存
 */
export async function saveAnswer(
  diagnosisId: number,
  questionId: number,
  answerValue: number,
  comment?: string
): Promise<void> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/answers`,
    {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        question_id: questionId,
        answer_value: answerValue,
        comment: comment || '',
      }),
    }
  );

  if (!response.ok) {
    throw new Error('回答の保存に失敗しました');
  }
}

/**
 * 下書きを保存
 */
export async function saveDraft(diagnosisId: number): Promise<void> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/save-draft`,
    {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
    }
  );

  if (!response.ok) {
    throw new Error('下書きの保存に失敗しました');
  }
}

/**
 * 満足度診断を完了
 */
export async function finishDiagnosis(diagnosisId: number): Promise<{ diagnosisId: number }> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/finish`,
    {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
    }
  );

  if (!response.ok) {
    throw new Error('診断の完了に失敗しました');
  }

  return response.json();
}

/**
 * 重要度回答を保存
 */
export async function saveImportanceAnswer(
  diagnosisId: number,
  questionId: number,
  importanceValue: number
): Promise<void> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/importance-answers`,
    {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        question_id: questionId,
        importance_value: importanceValue,
      }),
    }
  );

  if (!response.ok) {
    throw new Error('重要度回答の保存に失敗しました');
  }
}

/**
 * 重要度診断を完了
 */
export async function finishImportanceDiagnosis(
  diagnosisId: number
): Promise<{ diagnosisId: number }> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/finish-importance`,
    {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
    }
  );

  if (!response.ok) {
    throw new Error('重要度診断の完了に失敗しました');
  }

  return response.json();
}

/**
 * 診断結果を取得
 */
export async function getDiagnosisResult(diagnosisId: number): Promise<DiagnosisResult> {
  const response = await fetch(
    `${API_BASE_URL}/career-satisfaction-diagnosis/${diagnosisId}/result`,
    {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
    }
  );

  if (!response.ok) {
    throw new Error('診断結果の取得に失敗しました');
  }

  return response.json();
}

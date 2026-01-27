// 型定義ファイル

export interface CareerSatisfactionDiagnosis {
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

export interface CareerSatisfactionDiagnosisAnswer {
  id: number;
  career_satisfaction_diagnosis_id: number;
  question_id: number;
  answer_value: number; // 1-5
  comment: string | null;
  created_at: string;
  updated_at: string;
}

export interface CareerSatisfactionDiagnosisImportanceAnswer {
  id: number;
  career_satisfaction_diagnosis_id: number;
  question_id: number;
  importance_value: number; // 1-5
  created_at: string;
  updated_at: string;
}

export interface Question {
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

export interface DiagnosisResult {
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

export interface AnswerData {
  answer_value: number | null;
  comment: string;
}

export interface DiagnosisFormState {
  questions: Question[];
  currentIndex: number;
  answers: Record<number, AnswerData>;
  diagnosisId: number | null;
}

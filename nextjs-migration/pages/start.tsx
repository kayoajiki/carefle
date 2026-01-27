'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import DiagnosisForm from '../components/DiagnosisForm';
import { startDiagnosis, getQuestions } from '../api';
import { Question, AnswerData } from '../types';

export default function DiagnosisStartPage() {
  const router = useRouter();
  const [questions, setQuestions] = useState<Question[]>([]);
  const [answers, setAnswers] = useState<Record<number, AnswerData>>({});
  const [diagnosisId, setDiagnosisId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const initialize = async () => {
      try {
        // 質問を取得
        const fetchedQuestions = await getQuestions('work');
        setQuestions(fetchedQuestions);

        // 診断を開始（既存の下書きを取得または新規作成）
        const formState = await startDiagnosis();
        setDiagnosisId(formState.diagnosisId);
        setAnswers(formState.answers);
      } catch (err) {
        console.error('初期化エラー:', err);
        setError('診断の開始に失敗しました');
      } finally {
        setLoading(false);
      }
    };

    initialize();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7FF] flex items-center justify-center">
        <div className="text-center">
          <div className="body-large text-[#2E5C8A]">読み込み中...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-[#F0F7FF] flex items-center justify-center">
        <div className="text-center">
          <div className="body-large text-red-600 mb-4">{error}</div>
          <button
            className="btn-secondary"
            onClick={() => window.location.reload()}
          >
            再試行
          </button>
        </div>
      </div>
    );
  }

  if (!diagnosisId || questions.length === 0) {
    return (
      <div className="min-h-screen bg-[#F0F7FF] flex items-center justify-center">
        <div className="text-center">
          <div className="body-large text-[#2E5C8A]">
            診断を開始できませんでした
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#F0F7FF] flex flex-col items-center content-padding section-spacing-sm">
      {/* Header / Intro */}
      <div className="w-full max-w-2xl mb-12 text-center">
        <h1 className="heading-2 mb-4">職業満足度診断</h1>
        <p className="body-large">
          今の仕事との「関係」を、判断や圧力なく、安全に言語化します。
          <br />
          あなたの状態を「問題」ではなく「進捗レポート」として可視化し、
          <br />
          次のステップを一緒に考えていきましょう。
        </p>
      </div>

      {/* Diagnosis Form */}
      <div className="w-full max-w-2xl">
        <DiagnosisForm
          questions={questions}
          initialAnswers={answers}
          diagnosisId={diagnosisId}
        />
      </div>

      {/* Footer small note */}
      <div className="w-full max-w-2xl text-center body-small mt-12">
        回答はあなた専用の記録として保存され、あとから見返せます。
      </div>
    </div>
  );
}

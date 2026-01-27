'use client';

import { useEffect, useState } from 'react';
import { useRouter, useParams } from 'next/navigation';
import ImportanceForm from '../components/ImportanceForm';
import { getQuestions } from '../api';
import { Question } from '../types';

export default function ImportancePage() {
  const router = useRouter();
  const params = useParams();
  const diagnosisId = parseInt(params.id as string, 10);

  const [questions, setQuestions] = useState<Question[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const initialize = async () => {
      try {
        const fetchedQuestions = await getQuestions('work');
        setQuestions(fetchedQuestions);
      } catch (err) {
        console.error('初期化エラー:', err);
        setError('質問の取得に失敗しました');
      } finally {
        setLoading(false);
      }
    };

    if (diagnosisId) {
      initialize();
    }
  }, [diagnosisId]);

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

  return <ImportanceForm questions={questions} diagnosisId={diagnosisId} />;
}

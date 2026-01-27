'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import ResultView from '../components/ResultView';
import { getDiagnosisResult } from '../api';
import { DiagnosisResult } from '../types';

export default function ResultPage() {
  const params = useParams();
  const diagnosisId = parseInt(params.id as string, 10);

  const [result, setResult] = useState<DiagnosisResult | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchResult = async () => {
      try {
        const data = await getDiagnosisResult(diagnosisId);
        setResult(data);
      } catch (err) {
        console.error('結果取得エラー:', err);
        setError('診断結果の取得に失敗しました');
      } finally {
        setLoading(false);
      }
    };

    if (diagnosisId) {
      fetchResult();
    }
  }, [diagnosisId]);

  if (loading) {
    return (
      <div className="min-h-screen bg-[#EAF3FF] flex items-center justify-center">
        <div className="text-center">
          <div className="body-large text-[#2E5C8A]">読み込み中...</div>
        </div>
      </div>
    );
  }

  if (error || !result) {
    return (
      <div className="min-h-screen bg-[#EAF3FF] flex items-center justify-center">
        <div className="text-center">
          <div className="body-large text-red-600 mb-4">
            {error || '診断結果が見つかりませんでした'}
          </div>
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

  return <ResultView result={result} />;
}

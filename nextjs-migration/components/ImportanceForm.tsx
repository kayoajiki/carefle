'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Question } from '../types';
import { saveImportanceAnswer, finishImportanceDiagnosis } from '../api';

interface ImportanceFormProps {
  questions: Question[];
  diagnosisId: number;
}

// 固定テキスト（順番は Question::orderBy('order') の順に対応）
const fixedTextsByIndex = [
  '勤める会社の大義・ビジョン・目的に共感できることはあなたにとってどれくらい重要ですか？',
  '仕事にやりがい・価値を感じられることはあなたにとってどれくらい重要ですか？',
  '持っている強み・適性を仕事で活かすことはあなたにとってどれくらい重要ですか？',
  '「ずっとこの人と働きたい」と思える同僚・上司・部下がいることは、あなたにとってどれくらい重要ですか？',
  '一定の生活リズムや、バランスを保ちながら働くことはあなたにとってどれくらい重要ですか？',
  '収入や待遇はどれくらい重要ですか？',
  '仕事における肩書や評価はあなたにとってどれくらい重要ですか？',
  '仕事において成長実感を感じられることはあなたにとってどれくらい重要ですか？',
  '今の家族との関係、家庭生活、子育てなど仕事を除く生活全般が充実・満足していることは、あなたにとってどれくらい重要ですか？',
  '友人・家族を除く人間関係が充実・満足していることは、あなたにとってどれくらい重要ですか？',
  '自分のための時間（休息・趣味）をちゃんと確保できていることはあなたにとってどれくらい重要ですか？',
  '本業以外の活動（副業/事業/挑戦）を実施できていることや、充実していることは、あなたにとってどれくらい重要ですか？',
  '体調・メンタル・睡眠などに気を配った健康的な生活が送れていることはあなたにとってどれくらい重要ですか？',
  '将来に対する貯蓄・資産形成等ができていることはあなたにとってどれくらい重要ですか？',
];

const importanceOptions = [
  { value: 5, label: 'とても重要' },
  { value: 4, label: '重要' },
  { value: 3, label: 'どちらとも言えない' },
  { value: 2, label: '重要でない' },
  { value: 1, label: '全く重要でない' },
];

export default function ImportanceForm({
  questions,
  diagnosisId,
}: ImportanceFormProps) {
  const router = useRouter();
  const [currentIndex, setCurrentIndex] = useState(0);
  const [answers, setAnswers] = useState<Record<number, number | null>>({});
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const currentQuestion = questions[currentIndex];
  const currentAnswer = currentQuestion ? answers[currentQuestion.id] ?? null : null;

  // 表示テキストを取得
  const getDisplayText = (question: Question, index: number): string => {
    return fixedTextsByIndex[index] ?? toImportanceText(question.text);
  };

  // 元の質問文を重要度の質問文に変換
  const toImportanceText = (original: string): string => {
    let text = original.trim();
    // 語尾の「ですか？」や「か？」などを落として語幹を作る
    text = text.replace(/(ですか\?|でしょうか\?|か\?)$/u, '');
    // 主語の調整（あなたが/あなたの/… を簡易置換）
    text = text.replace(/^あなた[はがの]/u, '');
    // 重要度の定型文を付与
    return text.trim() + 'はあなたにとってどれくらい重要ですか？';
  };

  // オプション選択
  const handleSelectOption = async (value: number) => {
    if (!currentQuestion) return;

    setAnswers({
      ...answers,
      [currentQuestion.id]: value,
    });

    try {
      await saveImportanceAnswer(diagnosisId, currentQuestion.id, value);
    } catch (err) {
      console.error('重要度回答の保存に失敗しました:', err);
    }
  };

  // 次の質問へ
  const handleNext = () => {
    if (currentIndex < questions.length - 1) {
      setCurrentIndex(currentIndex + 1);
    } else {
      handleFinish();
    }
  };

  // 前の質問へ
  const handlePrev = () => {
    if (currentIndex > 0) {
      setCurrentIndex(currentIndex - 1);
    }
  };

  // 診断完了
  const handleFinish = async () => {
    // すべての質問に回答があるか確認
    const allAnswered = questions.every((q) => answers[q.id] !== null);

    if (!allAnswered) {
      setError('すべての質問に回答してください。重要度の入力は必須です。');
      return;
    }

    setIsSaving(true);
    setError(null);

    try {
      const result = await finishImportanceDiagnosis(diagnosisId);
      router.push(`/career-satisfaction-diagnosis/result/${result.diagnosisId}`);
    } catch (err) {
      setError('重要度診断の完了に失敗しました');
      console.error(err);
    } finally {
      setIsSaving(false);
    }
  };

  if (!currentQuestion) {
    return <div>質問を読み込めませんでした</div>;
  }

  return (
    <div className="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
      <div className="w-full max-w-3xl mx-auto card-refined p-8 md:p-10">
        <div className="mb-6 body-small">
          重要度チェック（必須） {currentIndex + 1}/{questions.length}
        </div>

        <h2 className="heading-3 text-xl mb-8">
          {getDisplayText(currentQuestion, currentIndex)}
        </h2>

        <div className="flex flex-col gap-4 mb-8">
          {importanceOptions.map((opt) => (
            <button
              key={opt.value}
              type="button"
              className={`w-full border-2 rounded-xl px-6 py-4 text-left transition-all duration-200 ${
                currentAnswer === opt.value
                  ? 'bg-[#E8F4FF] border-[#6BB6FF] shadow-sm'
                  : 'border-[#2E5C8A]/20 bg-white hover:border-[#6BB6FF]/50 hover:bg-[#F0F7FF]'
              }`}
              onClick={() => handleSelectOption(opt.value)}
            >
              <div className="body-text font-semibold text-[#2E5C8A]">
                {opt.label}
              </div>
            </button>
          ))}
        </div>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-800 text-xs p-3 rounded-md mb-4">
            {error}
          </div>
        )}

        <div className="flex items-center justify-between pt-6 border-t border-[#2E5C8A]/10">
          <button
            className="btn-secondary text-sm"
            onClick={handlePrev}
            disabled={currentIndex === 0 || isSaving}
          >
            戻る
          </button>
          <button
            className="btn-primary text-sm"
            onClick={handleNext}
            disabled={isSaving}
          >
            {currentIndex < questions.length - 1 ? '次へ' : '結果を見る'}
          </button>
        </div>
      </div>
    </div>
  );
}

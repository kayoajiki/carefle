'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Question, AnswerData } from '../types';
import { saveAnswer, saveDraft, finishDiagnosis } from '../api';

interface DiagnosisFormProps {
  questions: Question[];
  initialAnswers: Record<number, AnswerData>;
  diagnosisId: number;
}

export default function DiagnosisForm({
  questions,
  initialAnswers,
  diagnosisId,
}: DiagnosisFormProps) {
  const router = useRouter();
  const [currentIndex, setCurrentIndex] = useState(0);
  const [answers, setAnswers] = useState<Record<number, AnswerData>>(initialAnswers);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  const currentQuestion = questions[currentIndex];
  const currentAnswer = currentQuestion
    ? answers[currentQuestion.id] || { answer_value: null, comment: '' }
    : { answer_value: null, comment: '' };

  // 進捗率を計算
  const progressPercent = () => {
    const answeredCount = Object.values(answers).filter(
      (a) => a && a.answer_value !== null
    ).length;
    return Math.round((answeredCount / questions.length) * 100);
  };

  const isLast = currentIndex === questions.length - 1;

  // オプション選択
  const handleSelectOption = async (value: number) => {
    if (!currentQuestion) return;

    const newAnswers = {
      ...answers,
      [currentQuestion.id]: {
        ...currentAnswer,
        answer_value: value,
      },
    };
    setAnswers(newAnswers);

    try {
      await saveAnswer(
        diagnosisId,
        currentQuestion.id,
        value,
        currentAnswer.comment
      );
    } catch (err) {
      console.error('回答の保存に失敗しました:', err);
    }
  };

  // コメント更新
  const handleCommentChange = async (comment: string) => {
    if (!currentQuestion) return;

    const newAnswers = {
      ...answers,
      [currentQuestion.id]: {
        ...currentAnswer,
        comment,
      },
    };
    setAnswers(newAnswers);

    if (currentAnswer.answer_value !== null) {
      try {
        await saveAnswer(
          diagnosisId,
          currentQuestion.id,
          currentAnswer.answer_value,
          comment
        );
      } catch (err) {
        console.error('コメントの保存に失敗しました:', err);
      }
    }
  };

  // 次の質問へ
  const handleNext = () => {
    if (currentIndex < questions.length - 1) {
      setCurrentIndex(currentIndex + 1);
    }
  };

  // 前の質問へ
  const handlePrev = () => {
    if (currentIndex > 0) {
      setCurrentIndex(currentIndex - 1);
    }
  };

  // 下書き保存
  const handleSaveDraft = async () => {
    setIsSaving(true);
    setError(null);

    try {
      // すべての回答を保存
      for (const [questionId, answer] of Object.entries(answers)) {
        if (answer.answer_value !== null) {
          await saveAnswer(
            diagnosisId,
            parseInt(questionId),
            answer.answer_value,
            answer.comment
          );
        }
      }

      await saveDraft(diagnosisId);
      setMessage('回答を一時保存しました。あとで続きから再開できます。');
      router.push('/dashboard');
    } catch (err) {
      setError('下書きの保存に失敗しました');
      console.error(err);
    } finally {
      setIsSaving(false);
    }
  };

  // 診断完了
  const handleFinish = async () => {
    // すべての質問に回答があるか確認
    const allAnswered = questions.every(
      (q) => answers[q.id]?.answer_value !== null
    );

    if (!allAnswered) {
      setError('すべての質問に回答してください。');
      return;
    }

    setIsSaving(true);
    setError(null);

    try {
      // すべての回答を保存
      for (const [questionId, answer] of Object.entries(answers)) {
        if (answer.answer_value !== null) {
          await saveAnswer(
            diagnosisId,
            parseInt(questionId),
            answer.answer_value,
            answer.comment
          );
        }
      }

      const result = await finishDiagnosis(diagnosisId);
      router.push(`/career-satisfaction-diagnosis/importance/${result.diagnosisId}`);
    } catch (err) {
      setError('診断の完了に失敗しました');
      console.error(err);
    } finally {
      setIsSaving(false);
    }
  };

  if (!currentQuestion) {
    return <div>質問を読み込めませんでした</div>;
  }

  return (
    <div className="card-refined p-8 flex flex-col gap-8">
      {message && (
        <div className="bg-green-50 border border-green-200 text-green-800 text-xs p-3 rounded-md">
          {message}
        </div>
      )}

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-800 text-xs p-3 rounded-md">
          {error}
        </div>
      )}

      {/* 進捗バー */}
      <div>
        <div className="flex justify-between items-baseline mb-3">
          <div className="body-small font-medium text-[#2E5C8A]">
            Q{currentIndex + 1}/{questions.length}
          </div>
          <div className="body-small">約5分で完了します</div>
        </div>
        <div className="w-full bg-[#F0F7FF] rounded-full h-3 overflow-hidden">
          <div
            className="h-3 bg-[#6BB6FF] transition-all duration-300 rounded-full"
            style={{ width: `${progressPercent()}%` }}
          />
        </div>
      </div>

      {/* 質問テキスト */}
      <div className="mb-6">
        <h2 className="heading-3 text-xl mb-3">{currentQuestion.text}</h2>
        {currentQuestion.helper && (
          <p className="body-text">{currentQuestion.helper}</p>
        )}
      </div>

      {/* 回答オプション */}
      <div className="flex flex-col gap-4">
        {currentQuestion.options.map((opt) => (
          <button
            key={opt.value}
            type="button"
            className={`w-full border-2 rounded-xl px-6 py-4 text-left transition-all duration-200 ${
              currentAnswer.answer_value === opt.value
                ? 'bg-[#6BB6FF] text-[#2E5C8A] border-[#6BB6FF] shadow-sm'
                : 'border-[#2E5C8A]/20 bg-white hover:border-[#6BB6FF]/50 hover:bg-[#F0F7FF]'
            }`}
            onClick={() => handleSelectOption(opt.value)}
          >
            <div className="body-text font-semibold mb-1">{opt.label}</div>
            <div className="body-small">{opt.desc}</div>
          </button>
        ))}
      </div>

      {/* コメント入力 */}
      <div className="flex flex-col gap-3">
        <label className="body-small font-medium text-[#2E5C8A]">
          よければ一言メモ（任意）
        </label>
        <textarea
          className="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
          rows={4}
          placeholder="今一番引っ掛かっていること、嬉しいこと、しんどいことなど自由に。（診断には影響しません）"
          value={currentAnswer.comment}
          onChange={(e) => handleCommentChange(e.target.value)}
        />
      </div>

      {/* ナビゲーションボタン */}
      <div className="flex items-center justify-between pt-6 border-t border-[#2E5C8A]/10">
        <button
          className="body-small text-[#1E3A5F] underline underline-offset-2 hover:text-[#2E5C8A] transition-colors"
          onClick={handleSaveDraft}
          disabled={isSaving}
        >
          いったん保存してあとで続ける
        </button>

        <div className="flex gap-4">
          {currentIndex > 0 && (
            <button
              className="btn-secondary text-sm"
              onClick={handlePrev}
              disabled={isSaving}
            >
              戻る
            </button>
          )}

          <button
            className="btn-primary text-sm"
            onClick={isLast ? handleFinish : handleNext}
            disabled={isSaving}
          >
            {isLast ? '重要度を入力する' : '次へ'}
          </button>
        </div>
      </div>
    </div>
  );
}

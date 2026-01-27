'use client';

import { DiagnosisResult } from '../types';
import RadarChart from './RadarChart';
import Link from 'next/link';

interface ResultViewProps {
  result: DiagnosisResult;
}

const relationshipPatterns: Record<string, { main: string; sub: string }> = {
  PATTERN_1: {
    main: '全体的にバランスが取れている距離感',
    sub: '今の仕事を続けることに大きな不安はなく、落ち着いてこれからのことを考えられる状態です。',
  },
  PATTERN_2: {
    main: '一部に軽微なギャップがあるものの、全体的には安定している距離感',
    sub: '今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。',
  },
  PATTERN_3: {
    main: '特定の領域で理想とのギャップを感じている距離感',
    sub: '今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。',
  },
  PATTERN_4: {
    main: '複数の領域で軽微なギャップを感じている距離感',
    sub: '今の仕事を続けることもできる一方で、このままで良いのかは一度立ち止まって考えたい状態です。',
  },
  PATTERN_5: {
    main: '特定の領域で中程度のギャップを感じている距離感',
    sub: '今の仕事を続けることに迷いが生まれやすく、一度立ち止まって整理したい状態です。',
  },
  PATTERN_6: {
    main: '複数の領域で中程度のギャップを感じている距離感',
    sub: '今の仕事を続けることに迷いが生まれやすく、一度立ち止まって整理したい状態です。',
  },
  PATTERN_7: {
    main: '深刻なギャップを感じている距離感',
    sub: '今の仕事を続けることに大きな迷いがあり、一度立ち止まって整理することが大切な状態です。',
  },
  PATTERN_8: {
    main: '複数の領域でギャップがあり、満足度も低い距離感',
    sub: '今の仕事を続けることに大きな迷いがあり、一度立ち止まって整理することが大切な状態です。',
  },
  PATTERN_DEFAULT: {
    main: '今の仕事との距離感を見つめ直している状態',
    sub: '診断結果から、今の仕事との関わり方について一度立ち止まって考えてみる時期に来ているようです。',
  },
};

const summaryPatterns: Record<string, string[]> = {
  SUMMARY_C_HIGH: ['大きな不満があるわけではない', '納得感が保たれている', '今の状態を維持できる'],
  SUMMARY_C_MID: ['大きな不満があるわけではない', '納得感が少しずつ薄れている', '気持ちの置きどころを探している段階'],
  SUMMARY_A_HIGH: ['大きな不満があるわけではない', 'ただし、納得感が少しずつ薄れている', '気持ちの置きどころを探している段階'],
  SUMMARY_A_MID: ['一部の領域で不満を感じている', '納得感が薄れている', '気持ちの置きどころを探している段階'],
  SUMMARY_B_MID: ['複数の領域で不満を感じている', '納得感が薄れている', '気持ちの置きどころを探している段階'],
  SUMMARY_B_LOW: ['複数の領域で大きな不満を感じている', '納得感が大きく薄れている', '一度立ち止まって整理したい段階'],
  SUMMARY_DEFAULT: ['今の状態を客観的に見つめ直す段階', '納得感の源泉を確認する必要がある', '無理をせず、自分のペースで考える'],
};

const stuckPointMessages: Record<string, Record<string, string>> = {
  people: {
    mild: '日々の仕事に支障が出るほどではありませんが、「この人たちと長く一緒に働きたいか」と考えると、少し迷いが生まれやすい状態です。',
    moderate: '周囲との関係性において、自分らしさを出しにくい感覚や、価値観のズレを見過ごせなくなってきているようです。',
    severe: '人間関係におけるストレスや違和感が大きく、今の環境で自分を保ち続けることに限界を感じ始めている可能性があります。',
  },
  profession: {
    mild: '役割や期待は理解しているものの、自分が本当にやりたいこととの間にわずかなズレを感じやすくなっています。',
    moderate: '仕事の内容が自分の強みや価値観と合っていない感覚が強く、このまま続けていくことに疑問を感じている状態です。',
    severe: '仕事の意義や自分の適性に対して強い乖離を感じており、キャリアの方向性を根本から見直したい時期かもしれません。',
  },
  progress: {
    mild: '業務はこなせている一方で、「この期間で何が積み上がったか」を明確に言葉にしにくい感覚があります。',
    moderate: '自身の成長が停滞している感覚があり、今の環境で得られる学びに限界を感じ始めているようです。',
    severe: '今の仕事が自分の将来に繋がっている実感が乏しく、時間を浪費しているような強い焦燥感があるかもしれません。',
  },
  purpose: {
    mild: '仕事の意味や目的は理解しているものの、自分にとっての意義を再確認したい気持ちが生まれやすい状態です。',
    moderate: '組織の目指す方向と自分の想いが重なりにくくなっており、仕事への情熱を維持しにくい感覚があります。',
    severe: '会社のビジョンや目的に対して強い違和感があり、今の場所で働く理由を見失いつつある状態です。',
  },
  privilege: {
    mild: '環境や待遇面では大きな不満はないものの、長期的な視点で考えると少し不安を感じやすい状態です。',
    moderate: '給与や労働時間、評価などの条件面において、自分の貢献に見合っていないという不満が強まっているようです。',
    severe: '生活リズムの乱れや待遇への強い不満があり、今の環境を維持することが心身の負担になっている可能性があります。',
  },
};

const safeZoneMessages: Record<string, string> = {
  people: '信頼できる仲間に恵まれており、心理的な安全性が保たれていることが、あなたにとって大きな支えになっています。',
  profession: '自分の強みを活かせる役割を担えており、仕事そのものに対する納得感や手応えを感じられています。',
  progress: '日々の業務を通じて自身の成長を実感できており、キャリアの積み上げに対する安心感があります。',
  purpose: '組織の目的と自分の価値観が一致しており、仕事を通じて社会に貢献している実感が持てています。',
  privilege: '働く環境や条件面での満足度が高く、落ち着いてこれからのことを考えられる安定した土台があります。',
};

export default function ResultView({ result }: ResultViewProps) {
  const {
    diagnosis,
    radarLabels,
    radarWorkData,
    importanceDataset,
    stuckPointCount,
    stuckPointDetails,
    safeZoneDetails,
    stateType,
    relationshipPattern,
    summaryPattern,
    continuationPosition,
  } = result;

  const rel = relationshipPatterns[relationshipPattern] || relationshipPatterns.PATTERN_DEFAULT;
  const summary = summaryPatterns[summaryPattern] || summaryPatterns.SUMMARY_DEFAULT;

  const getPosText = (pos: number) => {
    if (pos >= 80) return '前向きに続けられる気持ち';
    if (pos >= 60) return '続けられる気持ちがある';
    if (pos >= 40) return '続けることに迷いがある';
    if (pos >= 20) return '続けることに大きな迷いがある';
    return '続けることに強い迷いがある';
  };

  return (
    <div className="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
      <div className="w-full max-w-6xl mx-auto space-y-10 text-[#1E3A5F]">
        {/* ファーストビュー：関係性の距離感 */}
        <div className="card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff] relative overflow-hidden">
          <div className="absolute top-0 right-0 p-4">
            {diagnosis.is_admin_visible && (
              <span className="text-xs px-3 py-1 rounded-full bg-green-50 border border-green-300 text-green-700 font-medium">
                管理者に共有中
              </span>
            )}
          </div>
          <div className="mb-8">
            <p className="body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2">
              いまは、こんな距離感にいます
            </p>
            <h1 className="heading-2 text-3xl md:text-4xl mb-4 text-[#1E3A5F]">
              {rel.main}
            </h1>
            <p className="body-large text-[#2E5C8A] leading-relaxed max-w-2xl">
              {rel.sub}
            </p>
          </div>

          {/* 図解（横棒線） */}
          <div className="mt-12 py-8 border-t border-[#6BB6FF]/20">
            <p className="body-small text-[#4B7BB5] mb-6 text-center font-semibold">
              今の仕事を「続けること」への気持ち
            </p>
            <div className="relative w-full max-w-2xl mx-auto h-12 flex items-center">
              <div className="absolute w-full h-1.5 bg-gradient-to-r from-[#6BB6FF] via-[#cbd5e1] to-[#FF9E6B] rounded-full"></div>
              <div className="absolute -top-6 left-0 body-small text-[#4B7BB5]">前向き</div>
              <div className="absolute -top-6 right-0 body-small text-[#FF9E6B]">迷いがある</div>
              <div
                className="absolute transition-all duration-1000 ease-out"
                style={{ left: `${continuationPosition}%` }}
              >
                <div className="relative flex flex-col items-center">
                  <div className="w-4 h-4 bg-[#1E3A5F] rounded-full border-2 border-white shadow-md"></div>
                  <div className="absolute top-6 whitespace-nowrap bg-[#1E3A5F] text-white text-[10px] px-2 py-1 rounded shadow-lg">
                    今のあなた: {getPosText(continuationPosition)}
                  </div>
                  <div className="w-0.5 h-4 bg-[#1E3A5F] -mt-1"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* 状態サマリー */}
        <div className="card-refined p-8 bg-white border border-[#6BB6FF]/10">
          <h2 className="heading-3 text-xl mb-6 flex items-center gap-2">
            <span className="text-2xl">📍</span> 今の状態をひとことで言うと
          </h2>
          <div className="bg-[#F0F7FF] rounded-2xl p-8 border border-[#6BB6FF]/20">
            <ul className="space-y-4">
              {summary.map((item, i) => (
                <li key={i} className="flex items-start gap-3 text-lg">
                  <span className="text-[#6BB6FF] mt-1">•</span>
                  <span>{item}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* 領域別の引っかかり方 */}
        {stuckPointCount > 0 && (
          <div className="space-y-6">
            <h2 className="heading-3 text-xl flex items-center gap-2 px-2">
              <span className="text-2xl">⚡️</span> 今、気持ちが揺れやすいポイント
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {Object.entries(stuckPointDetails).map(([pillar, detail]) => {
                const diff = detail.diff || 0;
                const severity = diff < -20 ? 'severe' : diff < -10 ? 'moderate' : 'mild';
                const msg = stuckPointMessages[pillar]?.[severity] || '';

                return (
                  <div
                    key={pillar}
                    className={`card-refined p-6 bg-white border-l-4 ${
                      severity === 'severe'
                        ? 'border-orange-400'
                        : severity === 'moderate'
                        ? 'border-orange-300'
                        : 'border-blue-300'
                    } hover:shadow-md transition-shadow`}
                  >
                    <h3 className="body-text font-bold mb-3 flex justify-between items-center">
                      {detail.label}
                      <span className="text-xs px-2 py-1 rounded bg-orange-50 text-orange-700">
                        ギャップあり
                      </span>
                    </h3>
                    <p className="body-text text-[#4A5A73] leading-relaxed mb-4">{msg}</p>

                    {detail.memos.length > 0 && (
                      <div className="mt-4 p-4 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] relative">
                        <span className="absolute -top-2.5 left-4 px-2 bg-[#F8FAFC] text-[10px] text-[#94A3B8] font-bold tracking-wider">
                          あなたのメモ
                        </span>
                        <div className="space-y-2">
                          {detail.memos.map((memo, i) => (
                            <p key={i} className="body-small italic text-[#64748B]">
                              「{memo}」
                            </p>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* 安心ゾーン */}
        {Object.keys(safeZoneDetails).length > 0 && (
          <div className="space-y-6">
            <h2 className="heading-3 text-xl flex items-center gap-2 px-2">
              <span className="text-2xl">🌱</span> 今、比較的安定しているところ
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {Object.entries(safeZoneDetails).map(([pillar, detail]) => (
                <div key={pillar} className="card-refined p-6 bg-green-50/30 border-l-4 border-green-300">
                  <h3 className="body-text font-bold mb-3">{detail.label}</h3>
                  <p className="body-text text-[#4A5A73] leading-relaxed mb-4">
                    {safeZoneMessages[pillar] || ''}
                  </p>

                  {detail.memos.length > 0 && (
                    <div className="mt-4 p-4 bg-white/60 rounded-xl border border-green-100 relative">
                      <span className="absolute -top-2.5 left-4 px-2 bg-green-50/30 text-[10px] text-[#86B88F] font-bold tracking-wider">
                        あなたのメモ
                      </span>
                      <div className="space-y-2">
                        {detail.memos.map((memo, i) => (
                          <p key={i} className="body-small italic text-[#86B88F]">
                            「{memo}」
                          </p>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* レーダーチャート */}
        <div className="card-refined p-8 bg-white border border-[#6BB6FF]/10">
          <h2 className="heading-3 text-xl mb-4 text-center">全体バランスの可視化</h2>
          <div className="max-w-md mx-auto">
            <RadarChart
              labels={radarLabels}
              workData={radarWorkData}
              importanceData={importanceDataset}
            />
          </div>
        </div>

        {/* 次の一歩 */}
        <div className="card-refined p-8 bg-gradient-to-br from-[#1E3A5F] to-[#2E5C8A] text-white">
          <h2 className="heading-3 text-xl mb-6 flex items-center gap-2">
            <span className="text-2xl">🚀</span> 今の距離感にいる人が、よく選ぶ行動
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            {[
              '今の仕事で「何が引っかかっているか」を整理する',
              '他の働き方や選択肢の話を聞いてみる',
              'あえて何も決めず、少し様子を見る',
              '信頼できる人に考えを話してみる',
            ].map((text, i) => (
              <div
                key={i}
                className="bg-white/10 p-4 rounded-xl border border-white/20 hover:bg-white/20 transition-colors"
              >
                <p className="body-text">{text}</p>
              </div>
            ))}
          </div>

          <div className="flex flex-col sm:flex-row justify-center gap-4">
            <Link
              href="/dashboard"
              className="px-8 py-3 rounded-full bg-white text-[#1E3A5F] font-bold text-center hover:bg-opacity-90 transition-all shadow-lg"
            >
              診断を終えてホームへ
            </Link>
            <Link
              href="/career-satisfaction-diagnosis/start"
              className="px-8 py-3 rounded-full bg-transparent border-2 border-white text-white font-bold text-center hover:bg-white/10 transition-all"
            >
              もう一度診断する
            </Link>
            {stateType === 'B' && (
              <Link
                href="#"
                className="px-8 py-3 rounded-full bg-orange-400 text-white font-bold text-center hover:bg-orange-500 transition-all shadow-lg"
              >
                誰かに話して整理する（面談）
              </Link>
            )}
          </div>
        </div>

        {!diagnosis.is_admin_visible && (
          <div className="text-center pb-10">
            <Link
              href={`/share-preview/career-satisfaction/${diagnosis.id}`}
              className="body-small text-[#4B7BB5] underline underline-offset-4 decoration-dotted"
            >
              この結果を管理者に共有する
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}

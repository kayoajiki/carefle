'use client';

import { DiagnosisResult } from '../types';
import RadarChart from './RadarChart';
import Link from 'next/link';

interface ResultViewProps {
  result: DiagnosisResult;
}

const stateTypeConfig = {
  A: {
    color: 'blue',
    bgColor: 'bg-blue-50',
    borderColor: 'border-blue-300',
    textColor: 'text-blue-800',
    title: '一人で内省を続けられる状態',
    description:
      '理想とギャップのある領域はありますが、混乱していません。言語化できたこと自体に納得感があり、方向性を急いでいない状態です。',
    actionTitle: 'CareFreの継続利用を推奨',
    actionDescription:
      '状態の変化を追跡し、思考の履歴を残し続けることで、急いで判断しない状態を維持できます。CareFreは判断を下すためのツールではなく、判断を急がない状態を保つための場所です。',
  },
  B: {
    color: 'orange',
    bgColor: 'bg-orange-50',
    borderColor: 'border-orange-300',
    textColor: 'text-orange-800',
    title: '一人だと堂々巡りになりやすい状態',
    description:
      '理想とギャップのある領域が集中しているか、複数の領域に深刻なギャップがあります。言語化はできたものの、解釈が揺れ、考えれば考えるほど不安が増える感覚があります。',
    actionTitle: '誰かと話して整理することを検討',
    actionDescription:
      '何が邪魔をしているかを明確にするため、一人で考え続けるのではなく、誰かと話して頭の中を整理することを選択肢として検討できます。今すぐ決める必要はありません。',
  },
  C: {
    color: 'green',
    bgColor: 'bg-green-50',
    borderColor: 'border-green-300',
    textColor: 'text-green-800',
    title: '今は動かない判断が妥当な状態',
    description:
      '安定ゾーンが広く、理想とギャップのある領域が軽微またはありません。大きなアクションは不要で、予防的な利用や定期的な状態確認が適切です。',
    actionTitle: 'CareFreの継続利用を推奨',
    actionDescription:
      '定期的な状態チェック（通知、再診断）を通じて、大きな問題が発生する前に早期に気づくことができます。',
  },
};

export default function ResultView({ result }: ResultViewProps) {
  const {
    diagnosis,
    workScore,
    radarLabels,
    radarWorkData,
    importanceDataset,
    stuckPointCount,
    stuckPointDetails,
    gapSummary,
    stateType,
  } = result;

  // 関係性の距離感を言語化
  const getRelationshipText = (): string => {
    if (stuckPointCount === 0) {
      return '現在の仕事との関係は、全体的にバランスが取れている状態です。';
    } else if (stuckPointCount <= 2 && (result.maxDiff ?? 0) >= -10) {
      return '現在の仕事との関係は、一部に軽微な理想とのギャップがあるものの、全体的には安定しています。';
    } else if (stuckPointCount <= 2 && (result.maxDiff ?? 0) < -10) {
      return '現在の仕事との関係は、特定の領域で理想とのギャップを感じている状態です。';
    } else {
      return '現在の仕事との関係は、複数の領域で理想とのギャップを感じている状態です。';
    }
  };

  const config = stateType
    ? stateTypeConfig[stateType] ?? stateTypeConfig.A
    : stateTypeConfig.A;

  return (
    <div className="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
      <div className="w-full max-w-6xl mx-auto space-y-10">
        {/* 第一ビュー：関係性の距離感 */}
        <div className="card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff]">
          <div className="mb-6">
            <div className="flex items-start justify-between mb-4">
              <div className="flex-1">
                <p className="body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2">
                  あなたと仕事の関係
                </p>
                <h1 className="heading-2 text-3xl md:text-4xl mb-4">
                  職業満足度診断結果
                </h1>
              </div>
              <div className="ml-4 flex items-center gap-3">
                {diagnosis.is_admin_visible ? (
                  <>
                    <span className="text-sm px-3 py-2 rounded-xl bg-green-50 border border-green-300 text-green-700 font-medium">
                      管理者に共有中
                    </span>
                    <button className="btn-secondary text-sm">
                      共有を解除
                    </button>
                  </>
                ) : (
                  <Link
                    href={`/share-preview/career-satisfaction/${diagnosis.id}`}
                    className="btn-secondary text-sm"
                  >
                    管理者に共有する
                  </Link>
                )}
              </div>
            </div>
          </div>
          <div className="bg-white/60 rounded-xl p-6 border-2 border-[#6BB6FF]/30">
            <p className="body-large text-[#1E3A5F] leading-relaxed">
              {getRelationshipText()}
            </p>
          </div>
        </div>

        {/* 状態サマリー */}
        <div className="card-refined p-8">
          <h2 className="heading-3 text-2xl mb-4 text-[#1E3A5F]">状態サマリー</h2>
          <p className="body-text text-[#4A5A73] mb-4">
            これは「問題」ではなく、「判断前の状態」としての進捗レポートです。
          </p>
          <div className="bg-[#F0F7FF] rounded-xl p-6 border border-[#6BB6FF]/20">
            <div className="space-y-3">
              <div className="flex items-start gap-3">
                <span className="text-2xl">📊</span>
                <div>
                  <p className="body-text font-semibold text-[#1E3A5F] mb-1">
                    満足度スコア
                  </p>
                  <p className="heading-1 text-4xl text-[#2E5C8A]">
                    {workScore}
                    <span className="text-xl">/100</span>
                  </p>
                </div>
              </div>
              {stuckPointCount > 0 && (
                <div className="flex items-start gap-3 pt-3 border-t border-[#6BB6FF]/20">
                  <span className="text-2xl">📍</span>
                  <div>
                    <p className="body-text font-semibold text-[#1E3A5F] mb-1">
                      理想とギャップのある領域
                    </p>
                    <p className="body-large text-[#2E5C8A]">
                      {stuckPointCount}個の領域で理想とギャップがあります
                    </p>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* 図表（ポジション可視化） */}
        <div className="card-refined p-8">
          <h2 className="heading-3 text-2xl mb-4 text-[#1E3A5F]">あなたの位置</h2>
          <p className="body-text text-[#4A5A73] mb-6">
            考え始めている位置を可視化しました。「ポジティブ/ロスト」ではなく、「今どこにいるか」を示しています。
          </p>
          <RadarChart
            labels={radarLabels}
            workData={radarWorkData}
            importanceData={importanceDataset}
          />
        </div>

        {/* 理想とギャップのある領域 */}
        {stuckPointCount > 0 && (
          <div className="card-refined p-8">
            <h2 className="heading-3 text-2xl mb-4 text-[#1E3A5F]">
              理想とギャップのある領域
            </h2>
            <p className="body-text text-[#4A5A73] mb-6">
              重要度（理想）と満足度にギャップがある領域です。深掘りはせず、現状を把握するための情報としてご覧ください。
            </p>
            <div className="space-y-4">
              {Object.values(stuckPointDetails).map((detail, index) => (
                <div
                  key={index}
                  className="bg-[#FFF4E6] rounded-xl p-6 border-2 border-orange-200"
                >
                  <div className="flex items-center justify-between mb-3">
                    <h3 className="body-text font-semibold text-[#1E3A5F]">
                      {detail.label}
                    </h3>
                    <span className="px-3 py-1 rounded-full bg-orange-100 text-orange-800 body-small font-semibold">
                      差分: {detail.diff}点
                    </span>
                  </div>
                  <div className="grid grid-cols-2 gap-4 mt-4">
                    <div>
                      <p className="body-small text-[#4A5A73] mb-1">満足度</p>
                      <p className="heading-2 text-2xl text-[#2E5C8A]">
                        {detail.satisfaction}
                        <span className="text-sm">/100</span>
                      </p>
                    </div>
                    <div>
                      <p className="body-small text-[#4A5A73] mb-1">重要度</p>
                      <p className="heading-2 text-2xl text-[#2E5C8A]">
                        {detail.importance}
                        <span className="text-sm">/100</span>
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* 今の状態について */}
        <div className="card-refined p-8 bg-gradient-to-br from-green-50 to-blue-50 border-2 border-green-200">
          <h2 className="heading-3 text-2xl mb-4 text-[#1E3A5F]">今の状態について</h2>

          {stuckPointCount > 0 ? (
            <div className="bg-white/60 rounded-xl p-6 border border-green-200 mb-4">
              <p className="body-text text-[#1E3A5F] mb-3 font-semibold">
                5つの領域のうち、{stuckPointCount}個の領域で理想とのギャップがあります。
              </p>
              <div className="space-y-2">
                {gapSummary.severe.length > 0 && (
                  <p className="body-text text-[#4A5A73]">
                    <span className="font-semibold text-orange-700">
                      特に大きいギャップ：
                    </span>
                    {gapSummary.severe.join('、')}
                  </p>
                )}
                {gapSummary.moderate.length > 0 && (
                  <p className="body-text text-[#4A5A73]">
                    <span className="font-semibold text-orange-600">
                      中程度のギャップ：
                    </span>
                    {gapSummary.moderate.join('、')}
                  </p>
                )}
                {gapSummary.mild.length > 0 && (
                  <p className="body-text text-[#4A5A73]">
                    <span className="font-semibold text-blue-600">軽微なギャップ：</span>
                    {gapSummary.mild.join('、')}
                  </p>
                )}
              </div>
            </div>
          ) : (
            <div className="bg-white/60 rounded-xl p-6 border border-green-200 mb-4">
              <p className="body-text text-[#1E3A5F]">
                5つの領域すべてで、理想と満足度のバランスが取れています。
              </p>
            </div>
          )}

          <p className="body-text text-[#4A5A73] leading-relaxed">
            理想とギャップのある領域があっても、それは「問題」ではなく「現状の把握」です。この状態を維持しながら、無理をせず、次のステップを一緒に考えていきましょう。
          </p>
        </div>

        {/* 状態判定結果（最重要） */}
        {stateType && (
          <div className={`card-refined p-8 ${config.bgColor} border-2 ${config.borderColor}`}>
            <div className="mb-6">
              <p className={`body-small uppercase tracking-[0.2em] ${config.textColor} mb-2`}>
                状態タイプ: {stateType}
              </p>
              <h2 className={`heading-2 text-3xl ${config.textColor} mb-3`}>
                {config.title}
              </h2>
              <p className={`body-large ${config.textColor} leading-relaxed`}>
                {config.description}
              </p>
            </div>

            <div
              className={`bg-white/60 rounded-xl p-6 ${
                stateType === 'A'
                  ? 'border-blue-200'
                  : stateType === 'B'
                  ? 'border-orange-200'
                  : 'border-green-200'
              } border`}
            >
              <h3 className={`body-text font-semibold ${config.textColor} mb-3`}>
                {config.actionTitle}
              </h3>
              <p className={`body-text ${config.textColor} leading-relaxed`}>
                {config.actionDescription}
              </p>

              {stateType === 'B' && (
                <div className="mt-6 pt-6 border-t border-orange-200">
                  <p className="body-text text-orange-800 mb-4">
                    誰かと話して整理する方法として、キャリハグ（１対１の面談）を選択肢として検討できます。
                  </p>
                  <p className="body-small text-orange-800 opacity-80">
                    ※今すぐ決める必要はありません。まずは自分の状態を理解することが大切です。
                  </p>
                </div>
              )}

              {stateType === 'C' && (
                <div className="mt-6 pt-6 border-t border-green-200">
                  <p className="body-text text-green-800">
                    CareFreの通知機能や再診断機能を活用して、定期的に状態を確認することをおすすめします。
                  </p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* ナビゲーション */}
        <div className="flex justify-center gap-4 pt-6">
          <Link href="/dashboard" className="btn-secondary">
            キャリフレを続ける
          </Link>
          <Link
            href="/career-satisfaction-diagnosis/start"
            className="btn-secondary"
          >
            もう一度診断する
          </Link>
          {stateType === 'B' && (
            <Link href="#" className="btn-primary">
              キャリハグについて詳しく
            </Link>
          )}
        </div>
      </div>
    </div>
  );
}

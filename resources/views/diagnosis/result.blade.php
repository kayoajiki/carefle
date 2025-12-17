<x-layouts.app.sidebar :title="'診断結果'">
    <flux:main>
<div class="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
    @php
        // 満足度と重要度の差分を計算して強みと伸ばしどころを決定
        $workPillarScores = $workPillarScores ?? [];
        $importanceWork = $importanceWork ?? [];
        $pillarLabels = $pillarLabels ?? [];
        
        $diffScores = []; // 満足度 - 重要度の差分
        $strongestDiff = null;
        $strongestKey = null;
        $weakestDiff = null;
        $weakestKey = null;
        
        foreach ($pillarLabels as $key => $label) {
            $pillarWorkScore = $workPillarScores[$key] ?? null;
            $importanceScore = $importanceWork[$key] ?? null;
            
            if ($pillarWorkScore !== null && $importanceScore !== null) {
                $diff = $pillarWorkScore - $importanceScore;
                $diffScores[$key] = $diff;
                
                // 強み: 満足度が重要度より高い（差分が最大）
                if ($diff > 0 && ($strongestDiff === null || $diff > $strongestDiff)) {
                    $strongestDiff = $diff;
                    $strongestKey = $key;
                }
                
                // 伸ばしどころ: 重要度が満足度より高い（差分が最小、つまりマイナスが最大）
                if ($diff < 0 && ($weakestDiff === null || $diff < $weakestDiff)) {
                    $weakestDiff = $diff;
                    $weakestKey = $key;
                }
            }
        }
        
        // 強みと伸ばしどころのラベルを取得
        $strongLabel = $strongestKey !== null ? ($pillarLabels[$strongestKey] ?? '未計測') : '未計測';
        $focusLabel = $weakestKey !== null ? ($pillarLabels[$weakestKey] ?? '未計測') : '未計測';
        
        // どちらも見つからない場合は従来の方法で計算（フォールバック）
        if ($strongLabel === '未計測' && $focusLabel === '未計測') {
            $workDataSet = $radarWorkData ?? [];
            $labels = $radarLabels ?? [];
            $minScore = filled($workDataSet) ? min($workDataSet) : null;
            $maxScore = filled($workDataSet) ? max($workDataSet) : null;
            $focusLabel = $minScore !== null ? ($labels[array_search($minScore, $workDataSet)] ?? '未計測') : '未計測';
            $strongLabel = $maxScore !== null ? ($labels[array_search($maxScore, $workDataSet)] ?? '未計測') : '未計測';
        }
        
        // ラベルからキーを抽出する関数
        $getPillarKey = function($label) {
            if ($label === '未計測') return null;
            // "Purpose（目的）" → "purpose" に変換
            if (preg_match('/^([A-Za-z]+)/', $label, $matches)) {
                return strtolower($matches[1]);
            }
            return null;
        };
        
        // 各項目ごとのフォーカス領域コメント
        $focusComments = [
            'purpose' => '目的意識を明確にすることで、仕事へのモチベーションが向上します。',
            'profession' => '職業スキルや専門性を高めることで、仕事への自信が生まれます。',
            'people' => '人間関係を整えることで、職場の雰囲気が良くなります。',
            'privilege' => '待遇や環境を改善することで、働きやすさが向上します。',
            'progress' => '成長実感を得ることで、仕事へのやりがいが生まれます。',
        ];
        
        // 各項目ごとの強みコメント
        $strengthComments = [
            'purpose' => '目的意識が高い強みを活かして、他の領域の改善にも良い影響を与えます。',
            'profession' => '職業スキルが高い強みを活かして、自信を持って他の領域にも取り組めます。',
            'people' => '人間関係が良好な強みを活かして、チームワークや協力関係を築けます。',
            'privilege' => '待遇や環境が整っている強みを活かして、安心して他の領域に取り組めます。',
            'progress' => '成長実感がある強みを活かして、前向きに他の領域にも挑戦できます。',
        ];
        
        // フォーカス領域と強みのコメントを取得
        $focusKey = $getPillarKey($focusLabel);
        $strongKey = $getPillarKey($strongLabel);
        $focusComment = $focusKey && isset($focusComments[$focusKey]) ? $focusComments[$focusKey] : 'この領域に取り組むことで、満足度の向上が期待できます。';
        $strengthComment = $strongKey && isset($strengthComments[$strongKey]) ? $strengthComments[$strongKey] : 'この領域の強みを意識して行動することで、他の領域の改善にも良い影響を与えます。';
        
        $balanceDelta = $workScore - $lifeScore;
        $absDelta = abs($balanceDelta);
        
        // 差分に応じたコメントを生成
        if ($balanceDelta === 0) {
            $balanceCopy = '満足度と重要度がバランスよく整っています';
        } elseif ($balanceDelta > 0) {
            // 満足度が重要度より高い場合（ポジティブ傾向）
            if ($absDelta >= 30) {
                $balanceCopy = '満足度が重要度よりかなり高いです。重要度の高い領域に意識を向けましょう。';
            } elseif ($absDelta >= 20) {
                $balanceCopy = '満足度が重要度より高いです。重要度の高い領域を意識すると更に充実します。';
            } elseif ($absDelta >= 10) {
                $balanceCopy = '満足度が重要度よりやや高いです。重要度を意識して取り組むとバランスが整います。';
            } else {
                $balanceCopy = '満足度が重要度よりわずかに高いです。現状は良好です。';
            }
        } else {
            // 重要度が満足度より高い場合（満足度が低い、ややネガティブだがフラットに）
            if ($absDelta >= 30) {
                $balanceCopy = '重要度が満足度よりかなり高いです。重要度の高い領域から優先的に改善を進めましょう。';
            } elseif ($absDelta >= 20) {
                $balanceCopy = '重要度が満足度より高いです。重要度の高い領域に重点的に取り組みましょう。';
            } elseif ($absDelta >= 10) {
                $balanceCopy = '重要度が満足度よりやや高いです。重要度の高い領域を意識して改善しましょう。';
            } else {
                $balanceCopy = '重要度が満足度よりわずかに高いです。重要度を意識して満足度を上げましょう。';
            }
        }
    @endphp

    <div class="w-full max-w-6xl mx-auto space-y-10">
        <!-- hero -->
        <div class="card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff]">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <p class="body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2">
                        Current Position
                    </p>
                    <h1 class="heading-2 text-3xl md:text-4xl mb-3">
            あなたの現在地レポート
        </h1>
                    <p class="body-large text-[#1E3A5F]">
                        「いまの仕事」と「いまの暮らし」の凸凹を俯瞰して、次の一歩に使えるヒントをまとめました。
        </p>
    </div>
                <div class="flex flex-wrap gap-3">
                    <span class="px-4 py-2 rounded-full bg-white text-[#2E5C8A] body-small font-semibold soft-shadow-refined">
                        {{ now()->format('Y.m.d') }} 更新
                    </span>
                    <span class="px-4 py-2 rounded-full bg-[#2E5C8A] text-white body-small font-semibold soft-shadow-refined">
                        診断ID：#{{ str_pad($diagnosis->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- score cards -->
        <div class="grid grid-cols-1 {{ $hasImportance ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-6">
            <div class="card-refined p-8 space-y-4">
                <div class="body-small font-medium text-[#4B7BB5]">満足度</div>
                <div class="heading-1 text-5xl text-[#1E3A5F]">
                    {{ $workScore }}<span class="text-2xl font-semibold"> /100</span>
                </div>
                <div class="w-full h-2.5 bg-[#E3ECF9] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#5B8DCC] to-[#2563EB]" style="width: {{ $workScore }}%;"></div>
                </div>
                <p class="body-small text-[#4A5A73]">
                    ビジョン共感・仕事の楽しさ・チームの相性・待遇など、働く場そのものへの納得度。
                </p>
            </div>

            @if($hasImportance)
                <div class="card-refined p-8 space-y-4">
                    <div class="body-small font-medium text-[#4B7BB5]">重要度</div>
                    <div class="heading-1 text-5xl text-[#1E3A5F]">
                        {{ $lifeScore }}<span class="text-2xl font-semibold"> /100</span>
                    </div>
                    <div class="w-full h-2.5 bg-[#E3ECF9] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#8FBEDC] to-[#4F9EDB]" style="width: {{ $lifeScore }}%;"></div>
                    </div>
                    <p class="body-small text-[#4A5A73]">
                        各領域への重要度の評価。満足度と比較することで、優先的に取り組むべき領域が明確になり、より効果的な行動計画を立てられます。
                    </p>
                </div>
            @else
                <div class="card-refined p-8 space-y-4 bg-gradient-to-br from-[#E3ECF9] to-[#F0F7FF]">
                    <div class="body-small font-medium text-[#4B7BB5] mb-2">重要度を入力すると、より深く理解できます</div>
                    <p class="body-text text-[#1E3A5F] mb-4">
                        現在は満足度のみの診断結果です。重要度を入力することで、満足度と重要度を比較し、優先的に取り組むべき領域が明確になります。
                    </p>
                    <a href="{{ route('diagnosis.importance', $diagnosis->id) }}" class="inline-block px-6 py-3 bg-[#6BB6FF] text-white rounded-lg font-semibold hover:bg-[#5B8DCC] transition-colors text-center">
                        重要度を入力する
                    </a>
                </div>
            @endif

            @if($hasImportance)
                <div class="card-refined p-8 space-y-4">
                    <div class="body-small font-medium text-[#4B7BB5]">満足度ー重要度</div>
                    <div class="heading-1 text-4xl text-[#1E3A5F]">
                        @if($balanceDelta === 0)
                            ±0
                        @else
                            {{ $balanceDelta > 0 ? '+' : '' }}{{ $balanceDelta }}
                        @endif
                    </div>
                    <p class="body-text text-[#1E3A5F]">
                        {{ $balanceCopy }}
                    </p>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="px-3 py-1 rounded-full bg-[#E3ECF9] text-[#2E5C8A] body-small">
                            強み：{{ $strongLabel }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-[#FFEED9] text-[#B45309] body-small">
                            伸ばしどころ：{{ $focusLabel }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- radar + insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-refined p-8">
                <div class="flex flex-col gap-2 mb-6">
                    <div class="heading-3 text-xl">
                        バランスチェック（レーダーチャート）
                    </div>
                    <p class="body-small text-[#4A5A73]">
                        凸は「安心・満足している領域」、凹は「これから呼吸を合わせたい領域」。重要度と重ねて眺めると、行動に移す順番が見えてきます。
                    </p>
                </div>

                <div class="w-full max-w-sm md:max-w-md mx-auto mb-6">
            <canvas id="radarChart" width="400" height="400"></canvas>
        </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-xs text-[#4A5A73]">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span> 満足度
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span> 重要度
                        </span>
                    </div>
                    <a href="{{ route('diagnosis.importance', ['id' => $diagnosis->id]) }}" class="btn-primary text-sm px-5 py-2">
                        今度は重要度を確認する
                    </a>
        </div>
    </div>

            <div class="card-refined p-8 flex flex-col gap-6">
                <div>
                    <div class="heading-3 text-xl mb-2">次に整えたいポイント</div>
                    <p class="body-small text-[#4A5A73]">
                        満足度と重要度の差分から、優先的に取り組むべき領域と活用できる強みをピックアップしました。
                    </p>
        </div>
                <div class="space-y-4">
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-[#F4F7FF] text-[#2E5C8A] flex items-center justify-center font-semibold">
                            01
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#2E5C8A] mb-1">フォーカス領域</p>
                            <p class="body-small text-[#4A5A73] mb-2">重要度が高いのに満足度が低い領域。優先的に改善すべきポイントです。</p>
                            <p class="heading-3 text-lg mb-2">{{ $focusLabel }}</p>
                            <p class="body-small text-[#4A5A73]">
                                {{ $focusComment }}
                            </p>
                        </div>
                    </div>
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4 bg-[#FDF7EE]">
                        <span class="w-12 h-12 rounded-2xl bg-white text-[#B45309] flex items-center justify-center font-semibold">
                            02
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#B45309] mb-1">活かしたい強み</p>
                            <p class="body-small text-[#72441A] mb-2">満足度が高く、余力がある領域。行動の下支えとして活用できる資産です。</p>
                            <p class="heading-3 text-lg mb-2">{{ $strongLabel }}</p>
                            <p class="body-small text-[#72441A]">
                                {{ $strengthComment }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- comments / reflection -->
        @if(!empty($answerNotes))
        <div class="card-refined p-8 space-y-6">
            <div>
                <div class="heading-3 text-xl mb-2">あなたのメモ</div>
                <p class="body-small text-[#4A5A73]">
                    セッションで深めたいキーワードを置き場として保存しています。読み返しながら、DiaryやMilestoneにも転記しておくと会話が滑らかになります。
                </p>
            </div>
            <div class="flex flex-col divide-y divide-[#2E5C8A]/10 gap-4">
                @foreach ($answerNotes as $note)
                    <div class="pt-4 first:pt-0">
                        <div class="body-small font-semibold text-[#2E5C8A] mb-1">
                            {{ $note['label'] }}
                        </div>
                        <div class="body-text whitespace-pre-line text-[#1E3A5F]">
                            {{ $note['comment'] }}
                        </div>
                    </div>
                @endforeach
        </div>
    </div>
    @endif

    <!-- actions -->
        <div class="flex flex-col md:flex-row gap-4">
            <a href="/diagnosis/start" class="btn-secondary flex-1 text-center">
            もう一度チェックする
        </a>
            <a href="/dashboard" class="btn-primary flex-1 text-center">
            ダッシュボードに戻る
        </a>
        </div>
    </div>
    </flux:main>
</x-layouts.app.sidebar>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let radarChartInstance = null;

function initRadarChart() {
    // Chart.jsが読み込まれているか確認
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded yet, retrying...');
        setTimeout(initRadarChart, 100);
        return;
    }

    // 既存のチャートがあれば破棄
    if (radarChartInstance) {
        radarChartInstance.destroy();
        radarChartInstance = null;
    }

    const canvas = document.getElementById('radarChart');
    if (!canvas) {
        console.error('Radar chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Could not get 2d context');
        return;
    }

    const radarLabels = @json($radarLabels ?? []);
    const workData = @json($radarWorkData ?? []);
    const lifeEdgeLeft = @json($lifeEdgeLeftData ?? []);
    const lifeEdgeRight = @json($lifeEdgeRightData ?? []);
    const lifePoint = @json($lifePointData ?? []);
    const lifeFill = @json($lifeFillData ?? []);
    const importanceData = @json($importanceDataset ?? []);
    const importanceLifeAvg = @json($importanceLifeAvg ?? null);

    console.log('Radar chart data:', {
        labels: radarLabels,
        workData: workData,
        lifeEdgeLeft: lifeEdgeLeft,
        lifeEdgeRight: lifeEdgeRight,
        lifePoint: lifePoint,
        lifeFill: lifeFill,
        importanceData: importanceData,
        importanceLifeAvg: importanceLifeAvg,
        importanceDataLength: importanceData.length,
        labelsLength: radarLabels.length
    });

    // データが空の場合はチャートを作成しない
    if (!radarLabels || radarLabels.length === 0) {
        console.warn('No radar chart labels found');
        return;
    }

    try {
        radarChartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: radarLabels,
        datasets: [
            {
                label: '満足度',
                data: workData,
                borderWidth: 3,
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,0.2)',
                pointBackgroundColor: '#2563EB',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
            // Life 左↔点 の線
            {
                label: 'Life-Link-L',
                data: lifeEdgeLeft,
                borderWidth: 2,
                borderColor: '#2563EB',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life 右↔点 の線
            {
                label: 'Life-Link-R',
                data: lifeEdgeRight,
                borderWidth: 2,
                borderColor: '#2563EB',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life の塗り（ワーク色と同系）
            {
                label: 'Life-Fill',
                data: lifeFill,
                borderWidth: 0,
                backgroundColor: 'rgba(37,99,235,0.15)',
                pointRadius: 0,
                spanGaps: true,
            },
            // 重要度（オレンジ系で温かみとコントラスト）
            {
                label: '重要度',
                data: importanceData,
                borderWidth: 3,
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245,158,11,0.15)',
                pointBackgroundColor: '#F59E0B',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                spanGaps: true,
            },
            // Life の点のみ
            {
                label: 'Life-Point',
                data: lifePoint,
                borderWidth: 0,
                showLine: false,
                backgroundColor: 'transparent',
                pointBackgroundColor: '#2563EB',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            r: {
                suggestedMin: 0,
                suggestedMax: 100,
                grid: { color: 'rgba(46,92,138,0.2)', lineWidth: 1 },
                angleLines: { color: 'rgba(46,92,138,0.2)', lineWidth: 1 },
                pointLabels: {
                    color: '#2E5C8A',
                    font: { size: 11 }
                },
                ticks: {
                    backdropColor: 'transparent',
                    color: '#1E3A5F',
                    font: { size: 10 },
                    stepSize: 20
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#1E3A5F',
                    font: { size: 11 },
                    // 「満足度」「重要度」だけを凡例に表示
                    filter: function(item) {
                        return item.text === '満足度' || item.text === '重要度';
                    }
                }
            }
        }
    }
    });
    } catch (error) {
        console.error('Error creating chart:', error);
    }
}

// Chart.jsの読み込み完了を待つ
function waitForChartJS(callback) {
    if (typeof Chart !== 'undefined') {
        callback();
    } else {
        // Chart.jsのスクリプトタグのonloadイベントを待つ
        const script = document.querySelector('script[src*="chart.js"]');
        if (script) {
            script.addEventListener('load', callback);
        } else {
            // フォールバック: 定期的にチェック
            setTimeout(() => waitForChartJS(callback), 50);
        }
    }
}

// DOMContentLoadedとLivewireナビゲーションの両方に対応
function initChartWhenReady() {
    waitForChartJS(() => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRadarChart);
    } else {
            // DOMが既に読み込まれている場合は少し遅延させてから実行
            setTimeout(initRadarChart, 50);
    }
    });
}

// Livewireのナビゲーション後にも実行
document.addEventListener('livewire:navigated', () => {
    waitForChartJS(() => {
        setTimeout(initRadarChart, 50);
    });
});

// 初回読み込み時にも実行
initChartWhenReady();
</script>
 
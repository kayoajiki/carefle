<x-layouts.app.sidebar :title="'診断結果'">
    <flux:main>
<div class="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
    @php
        $workDataSet = $radarWorkData ?? [];
        $labels = $radarLabels ?? [];
        $minScore = filled($workDataSet) ? min($workDataSet) : null;
        $maxScore = filled($workDataSet) ? max($workDataSet) : null;
        $focusLabel = $minScore !== null ? ($labels[array_search($minScore, $workDataSet)] ?? '未計測') : '未計測';
        $strongLabel = $maxScore !== null ? ($labels[array_search($maxScore, $workDataSet)] ?? '未計測') : '未計測';
        $balanceDelta = $workScore - $lifeScore;
        $balanceCopy = $balanceDelta === 0
            ? '仕事と暮らしが同じテンションで整っています'
            : ($balanceDelta > 0 ? '仕事側に余力がありそうです' : '暮らし側がより満ちています');
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card-refined p-8 space-y-4">
                <div class="body-small font-medium text-[#4B7BB5]">Work 満足度</div>
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

            <div class="card-refined p-8 space-y-4">
                <div class="body-small font-medium text-[#4B7BB5]">Life 満足度</div>
                <div class="heading-1 text-5xl text-[#1E3A5F]">
                    {{ $lifeScore }}<span class="text-2xl font-semibold"> /100</span>
                </div>
                <div class="w-full h-2.5 bg-[#E3ECF9] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#8FBEDC] to-[#4F9EDB]" style="width: {{ $lifeScore }}%;"></div>
                </div>
                <p class="body-small text-[#4A5A73]">
                    家族・健康・余暇・お金の安心感など、暮らしの土台がどれだけ柔らかく整っているか。
                </p>
            </div>

            <div class="card-refined p-8 space-y-4">
                <div class="body-small font-medium text-[#4B7BB5]">バランス & 次の視点</div>
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
                        スコアの凹み具合から、いま着手すると全体の呼吸が整いやすい領域をピックアップしました。
                    </p>
                </div>
                <div class="space-y-4">
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-[#F4F7FF] text-[#2E5C8A] flex items-center justify-center font-semibold">
                            01
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#2E5C8A] mb-1">フォーカス領域</p>
                            <p class="heading-3 text-lg mb-2">{{ $focusLabel }}</p>
                            <p class="body-small text-[#4A5A73]">
                                日常のリズムを乱しやすい要素。小さな行動で可視化し、Diaryに記録すると変化が追いやすくなります。
                            </p>
                        </div>
                    </div>
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4 bg-[#FDF7EE]">
                        <span class="w-12 h-12 rounded-2xl bg-white text-[#B45309] flex items-center justify-center font-semibold">
                            02
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#B45309] mb-1">活かしたい強み</p>
                            <p class="heading-3 text-lg mb-2">{{ $strongLabel }}</p>
                            <p class="body-small text-[#72441A]">
                                余力がある領域は、行動の下支えに使える資産。Diaryの「今日の出来事」に書き添えると、良い循環が印象に残ります。
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

    console.log('Radar chart data:', {
        labels: radarLabels,
        workData: workData,
        lifeEdgeLeft: lifeEdgeLeft,
        lifeEdgeRight: lifeEdgeRight,
        lifePoint: lifePoint,
        lifeFill: lifeFill,
        importanceData: importanceData
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
 


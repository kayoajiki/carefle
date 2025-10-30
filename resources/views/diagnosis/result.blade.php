<x-layouts.app.sidebar :title="'診断結果'">
    <flux:main>
<div class="min-h-screen bg-[#f2f7f5] px-4 py-8">
    <!-- heading -->
    <div class="w-full max-w-4xl mb-8">
        <h1 class="text-2xl font-semibold text-[#00473e]">
            あなたの現在地レポート
        </h1>
        <p class="text-sm text-[#475d5b] mt-2 leading-relaxed">
            「いまの仕事」と「いまの暮らし」の満足度を数値化しました。<br>
            グラフの凸凹が、次に整えるべきヒントになります。
        </p>
    </div>

    <!-- top scores -->
    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-xs text-[#475d5b] font-medium mb-1">Work 満足度</div>
            <div class="text-4xl font-bold text-[#00473e] leading-none">
                {{ $workScore }}<span class="text-lg font-semibold">/100</span>
            </div>
            <p class="text-[11px] text-[#475d5b] leading-snug mt-2">
                会社のビジョン・仕事内容・仲間・待遇・成長感など、働く環境そのものへの満足度。
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-xs text-[#475d5b] font-medium mb-1">Life 満足度</div>
            <div class="text-4xl font-bold text-[#00473e] leading-none">
                {{ $lifeScore }}<span class="text-lg font-semibold">/100</span>
            </div>
            <p class="text-[11px] text-[#475d5b] leading-snug mt-2">
                家族・人間関係・余暇・健康・お金の安心感など、暮らしの土台への満足度。
            </p>
        </div>
    </div>

    <!-- radar chart card -->
    <div class="w-full max-w-4xl bg-white rounded-xl shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
            <div>
                <div class="text-sm font-semibold text-[#00473e]">
                    バランスチェック（レーダーチャート）
                </div>
                <div class="text-[11px] text-[#475d5b] leading-snug mt-1">
                    凸が「満足・安定しているところ」、凹が「これから整えたいところ」。
                </div>
            </div>
        </div>

        <div class="w-full md:w-2/3 mx-auto">
            <canvas id="radarChart" width="400" height="400"></canvas>
        </div>
        <div class="mt-4 text-right">
            <a href="{{ route('diagnosis.importance', ['id' => $diagnosis->id]) }}" class="text-xs px-4 py-2 rounded-md font-semibold bg-[#60a5fa] text-white shadow-sm">今度は重要度を確認する</a>
        </div>
    </div>

    <!-- comments / reflection -->
    @if(!empty($answerNotes))
    <div class="w-full max-w-4xl bg-white rounded-xl shadow p-6 mb-16">
        <div class="text-sm font-semibold text-[#00473e] mb-2">
            あなたのメモ
        </div>
        <p class="text-[11px] text-[#475d5b] leading-snug mb-4">
            あなたが残してくれた一言メモは、次のセッションで深く扱う領域です。
        </p>

        <div class="flex flex-col divide-y divide-[#00473e]/10 text-sm text-[#00473e]">
            @foreach ($answerNotes as $note)
                <div class="py-3">
                    <div class="text-[11px] text-[#475d5b] mb-1 font-medium">
                        {{ $note['label'] }}
                    </div>
                    <div class="whitespace-pre-line">
                        {{ $note['comment'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- actions -->
    <div class="w-full max-w-4xl flex flex-col md:flex-row gap-4 mb-24">
        <a
            href="/diagnosis/start"
            class="flex-1 text-center text-xs px-4 py-3 rounded-md border border-[#00473e]/30 text-[#00473e] bg-white font-medium shadow-sm"
        >
            もう一度チェックする
        </a>
        <a
            href="/dashboard"
            class="flex-1 text-center text-xs px-4 py-3 rounded-md font-semibold bg-[#faae2b] text-[#00473e] shadow-sm"
        >
            ダッシュボードに戻る
        </a>
    </div>
    </flux:main>
</x-layouts.app.sidebar>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('radarChart').getContext('2d');

const radarLabels = @json($radarLabels);
const workData = @json($radarWorkData);
const lifeEdgeLeft = @json($lifeEdgeLeftData ?? []);
const lifeEdgeRight = @json($lifeEdgeRightData ?? []);
const lifePoint = @json($lifePointData ?? []);
const lifeFill = @json($lifeFillData ?? []);
const importanceData = @json($importanceDataset ?? []);

new Chart(ctx, {
    type: 'radar',
    data: {
        labels: radarLabels,
        datasets: [
            {
                label: '満足度',
                data: workData,
                borderWidth: 2,
                borderColor: '#00473e',
                backgroundColor: 'rgba(250,174,43,0.15)',
                pointBackgroundColor: '#00473e',
                pointBorderColor: '#00473e',
            },
            // Life 左↔点 の線
            {
                label: 'Life-Link-L',
                data: lifeEdgeLeft,
                borderWidth: 2,
                borderColor: '#00473e',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life 右↔点 の線
            {
                label: 'Life-Link-R',
                data: lifeEdgeRight,
                borderWidth: 2,
                borderColor: '#00473e',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life の塗り（ワーク色と同系）
            {
                label: 'Life-Fill',
                data: lifeFill,
                borderWidth: 0,
                backgroundColor: 'rgba(250,174,43,0.15)',
                pointRadius: 0,
                spanGaps: true,
            },
            // 重要度（青）
            {
                label: '重要度',
                data: importanceData,
                borderWidth: 2,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.10)',
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#3b82f6',
                spanGaps: true,
            },
            // Life の赤い点のみ
            {
                label: 'Life-Point',
                data: lifePoint,
                borderWidth: 0,
                showLine: false,
                backgroundColor: 'transparent',
                pointBackgroundColor: '#00473e',
                pointBorderColor: '#00473e',
                pointRadius: 4,
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
                grid: { color: 'rgba(0,71,62,0.15)' },
                angleLines: { color: 'rgba(0,71,62,0.15)' },
                pointLabels: {
                    color: '#00473e',
                    font: { size: 11 }
                },
                ticks: {
                    backdropColor: 'transparent',
                    color: '#475d5b',
                    font: { size: 10 },
                    stepSize: 20
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#475d5b',
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
</script>
 


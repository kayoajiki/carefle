@if($events->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
<x-admin.layouts.app title="人生史: {{ $user->name }}">
    <div class="min-h-screen w-full bg-[#F0F7FF] text-[#1E3A5F] content-padding section-spacing-sm">
        {{-- ヘッダー --}}
        <div class="max-w-6xl mx-auto mb-12">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
                <div>
                    <h1 class="heading-2 mb-4">
                        {{ $user->name }}さんの人生史一覧
                    </h1>
                    <p class="body-large">
                        これまでの人生の出来事を時系列で振り返ります。
                    </p>
                </div>
                <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" class="btn-secondary text-sm">
                    ユーザー詳細に戻る
                </a>
            </div>
        </div>

        @if($events->count() > 0)
            <div class="max-w-6xl mx-auto space-y-8">
                {{-- モチベーショングラフ --}}
                <div class="bg-white rounded-2xl shadow-md border border-[#2E5C8A]/10 p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-[#2E5C8A] mb-4">モチベーショングラフ</h3>
                    
                    <div class="relative w-full overflow-x-auto">
                        <div style="height: 250px; min-height: 250px; min-width: 300px;">
                            <canvas id="motivationChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- タイムラインと出来事一覧 --}}
                <div class="flex flex-col md:flex-row gap-4 md:gap-12">
                    {{-- 左側：タイムライン --}}
                    <div class="hidden md:block w-16 flex-shrink-0">
                        <div class="relative pl-8">
                            {{-- タイムライン --}}
                            <div class="relative">
                                
                                @foreach($years as $year)
                                    @php
                                        $yearEvents = $eventsByYear[$year];
                                    @endphp
                                    
                                    @foreach($yearEvents as $event)
                                        
                                        <div class="relative mb-4">
                                            {{-- カラフルなセグメント --}}
                                            <div 
                                                class="timeline-segment w-16 rounded-lg relative shadow-md border border-[#e5e7eb] overflow-hidden"
                                                data-event-id="event-{{ $year }}-{{ $event->id }}"
                                                style="background: {{ $event->timeline_color ?? '#FFFFFF' }};"
                                            >
                                                {{-- ラベル（縦書き） --}}
                                                <div class="absolute inset-0 flex items-center justify-center p-1">
                                                    <div class="w-full h-full">
                                                        <div class="w-full h-full text-[#2E5C8A] text-xs font-semibold"
                                                            style="writing-mode: vertical-rl; text-orientation: mixed;"
                                                        >
                                                            {{ $event->timeline_label ?? $year }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 右側：出来事一覧 --}}
                    <div class="flex-1 min-w-0">
                        <div class="space-y-4 md:space-y-4">
                            @foreach($years as $year)
                                @php
                                    $yearEvents = $eventsByYear[$year];
                                @endphp
                                
                                @foreach($yearEvents as $event)
                                    <div id="event-{{ $year }}-{{ $event->id }}" class="card-refined p-4 md:p-6 lg:p-8">
                                        {{-- ヘッダー --}}
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold text-[#2E5C8A] bg-[#F0F7FF] px-2 py-1 rounded">
                                                    {{ $event->year }}年
                                                </span>
                                            </div>
                                        </div>

                                        {{-- タイトル --}}
                                        <h3 class="text-base font-semibold text-[#2E5C8A] mb-2">
                                            {{ $event->title }}
                                        </h3>

                                        {{-- 内容 --}}
                                        @if($event->description)
                                            <div class="text-sm text-[#1E3A5F] leading-relaxed mt-2 whitespace-pre-line">
                                                {{ $event->description }}
                                            </div>
                                        @endif

                                        @if($event->motivation)
                                            <div class="mt-3 text-sm text-[#1E3A5F]">
                                                <span class="px-2 py-1 rounded-lg bg-[#F0F7FF] border border-[#2E5C8A]/10">
                                                    モチベーション: {{ $event->motivation }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-6xl mx-auto">
                <div class="card-refined border-2 border-dashed border-[#2E5C8A]/20 p-16 text-center">
                    <p class="text-sm text-[#1E3A5F]">
                        まだ出来事が登録されていません。
                    </p>
                </div>
            </div>
        @endif
    </div>

@if($events->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.jsの読み込みを待つ関数
function waitForChartJS(callback) {
    if (typeof Chart !== 'undefined') {
        callback();
    } else {
        const script = document.querySelector('script[src*="chart.js"]');
        if (script) {
            script.addEventListener('load', callback);
        } else {
            setTimeout(() => waitForChartJS(callback), 50);
        }
    }
}

function initMotivationChart() {
    const events = @json($events);
    
    if (events.length === 0) return;
    
    // データを準備
    const labels = events.map(e => e.year);
    const motivations = events.map(e => e.motivation);
    const titles = events.map(e => e.title);
    
    // グラフの設定
    const ctx = document.getElementById('motivationChart');
    if (!ctx) return;
    
    // 既存のグラフインスタンスを破棄
    if (window.motivationChartInstance) {
        window.motivationChartInstance.destroy();
    }
    
    window.motivationChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'モチベーション',
                data: motivations,
                borderColor: '#6BB6FF',
                backgroundColor: 'rgba(107, 182, 255, 0.1)',
                borderWidth: 2,
                pointRadius: 6,
                pointBackgroundColor: '#6BB6FF',
                pointBorderColor: '#2E5C8A',
                pointBorderWidth: 2,
                pointHoverRadius: 8,
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            return titles[index] || labels[index];
                        },
                        label: function(context) {
                            return 'モチベーション: ' + context.parsed.y;
                        }
                    }
                }
            },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            },
                            title: {
                                display: true,
                                text: 'モチベーション',
                                font: {
                                    size: window.innerWidth < 768 ? 12 : 14
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            },
                            title: {
                                display: true,
                                text: '年',
                                font: {
                                    size: window.innerWidth < 768 ? 12 : 14
                                }
                            }
                        }
                    }
        }
    });
}

// DOMContentLoadedとLivewireナビゲーションの両方に対応
function initChartWhenReady() {
    waitForChartJS(() => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMotivationChart);
        } else {
            setTimeout(initMotivationChart, 50);
        }
    });
}

// Livewireのナビゲーション後にも実行
document.addEventListener('livewire:navigated', () => {
    waitForChartJS(() => {
        setTimeout(initMotivationChart, 100);
    });
});

// 初回読み込み時にも実行
initChartWhenReady();
</script>

<script>
// 右側の出来事カードの高さに合わせて左側のタイムラインセグメントの高さを調整
function syncTimelineSegments() {
    const segments = document.querySelectorAll('.timeline-segment');
    segments.forEach(segment => {
        const eventId = segment.getAttribute('data-event-id');
        const eventCard = document.getElementById(eventId);
        if (eventCard && eventCard.offsetHeight > 0) {
            const cardHeight = eventCard.offsetHeight;
            segment.style.height = cardHeight + 'px';
        }
    });
}

// 複数のタイミングで実行を試みる関数
function initTimelineSync() {
    // 即座に実行
    syncTimelineSegments();
    
    // 短い遅延で実行（DOM要素が完全にレンダリングされるまで待つ）
    setTimeout(syncTimelineSegments, 50);
    setTimeout(syncTimelineSegments, 200);
    setTimeout(syncTimelineSegments, 500);
    
    // 画像が読み込まれた後にも実行
    window.addEventListener('load', function() {
        setTimeout(syncTimelineSegments, 100);
    });
}

// DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initTimelineSync();
});

// 既にDOMが読み込まれている場合（ページリロード後など）
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTimelineSync);
} else {
    initTimelineSync();
}

// ウィンドウリサイズ時にも再計算
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(syncTimelineSegments, 100);
});
</script>
@endif
</x-admin.layouts.app>

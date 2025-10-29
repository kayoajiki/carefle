<div class="min-h-screen w-full bg-[#f2f7f5] text-[#475d5b] px-4 py-6 md:px-8" x-data="{ showGraph: false, showTimeline: false }">
    {{-- ヘッダー --}}
    <div class="max-w-4xl mx-auto mb-6">
        <h1 class="text-xl font-semibold text-[#00473e]">
            人生史の作成
        </h1>
        <p class="text-sm text-[#475d5b] leading-relaxed mt-2">
            今まで生きてきた中で、自分にとって節目となった体験・大きな影響を与えた・印象に残っている体験を書き出してみてください。
        </p>
    </div>

    {{-- 入力フォームカード --}}
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-md border border-[#00332c]/10 p-4 md:p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="text-[#00473e] font-semibold">
                {{ $editingId ? '出来事を編集' : '新しい出来事を追加' }}
            </div>

            @if($editingId)
                <button
                    wire:click="cancelEdit"
                    class="text-xs underline text-[#475d5b] hover:text-[#00473e] transition">
                    編集をやめる
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- 西暦 --}}
            <div class="flex flex-col">
                <label class="text-xs font-medium text-[#00473e] mb-1">西暦 <span class="text-red-500">*</span></label>
                <input
                    type="number"
                    wire:model.defer="year"
                    class="w-full rounded-lg border border-[#00332c]/20 bg-[#f2f7f5] text-[#00473e] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="2008"
                />
                @error('year')
                    <span class="text-xs text-[#fa5246] mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- タイトル --}}
            <div class="flex flex-col md:col-span-1">
                <label class="text-xs font-medium text-[#00473e] mb-1">タイトル <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    wire:model.defer="title"
                    class="w-full rounded-lg border border-[#00332c]/20 bg-white text-[#00473e] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="大学入学 / 初めての転職 など"
                />
                @error('title')
                    <span class="text-xs text-[#fa5246] mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- 内容 --}}
            <div class="flex flex-col md:col-span-2">
                <label class="text-xs font-medium text-[#00473e] mb-1">内容</label>
                <textarea
                    wire:model.defer="description"
                    rows="3"
                    class="w-full rounded-lg border border-[#00332c]/20 bg-white text-[#00473e] px-3 py-2 text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="当時どんな状況で、どんな気持ちだったかを書いてください。"
                ></textarea>
                @error('description')
                    <span class="text-xs text-[#fa5246] mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- motivation & ボタン --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div class="flex flex-col">
                <label class="text-xs font-medium text-[#00473e] mb-2">
                    当時のモチベーション（0〜100）
                </label>
                <div class="flex items-center gap-3">
                    <input
                        type="range"
                        min="0"
                        max="100"
                        wire:model.defer="motivation"
                        class="w-full accent-[#faae2b]"
                    />
                    <div class="text-sm font-semibold text-[#00473e] w-10 text-right">
                        {{ $motivation }}
                    </div>
                </div>
                @error('motivation')
                    <span class="text-xs text-[#fa5246] mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-end justify-start md:justify-end">
                <button
                    wire:click="save"
                    class="w-full md:w-auto bg-[#faae2b] text-[#00473e] font-semibold text-sm rounded-xl px-4 py-2 shadow hover:opacity-90 active:scale-[0.99] transition">
                    {{ $editingId ? '更新する' : '追加する' }}
                </button>
            </div>
        </div>
    </div>

    {{-- 一覧 --}}
    <div class="max-w-4xl mx-auto">
        <h2 class="text-sm font-semibold text-[#00473e] mb-3">
            登録済みの出来事
        </h2>

        <div class="max-h-[480px] overflow-y-auto space-y-4 pr-1">
            @forelse ($events as $event)
                <div class="bg-white rounded-xl border border-[#00332c]/10 shadow-sm p-4 flex flex-col md:flex-row md:items-start md:justify-between">
                    {{-- 左側：本文 --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="text-base font-semibold text-[#00473e]">
                                {{ $event->year }}年
                            </div>
                            <div class="text-sm font-semibold text-[#00473e]">
                                {{ $event->title }}
                            </div>
                        </div>

                        @if($event->description)
                            <div class="text-sm text-[#475d5b] leading-relaxed mt-2 whitespace-pre-line">
                                {{ $event->description }}
                            </div>
                        @endif

                        <div class="text-xs text-[#475d5b] mt-3 flex items-center gap-2">
                            <span class="px-2 py-1 rounded-lg bg-[#f2f7f5] border border-[#00332c]/10 text-[#00473e] font-medium">
                                モチベーション {{ $event->motivation }}
                            </span>
                        </div>
                    </div>

                    {{-- 右側：アクション --}}
                    <div class="flex flex-row md:flex-col gap-3 mt-4 md:mt-0 md:ml-6 shrink-0">
                        <button
                            wire:click="edit({{ $event->id }})"
                            class="text-xs font-semibold px-3 py-1 rounded-lg border border-[#00332c]/20 text-[#00473e] bg-[#f2f7f5] hover:bg-[#faae2b]/20 transition">
                            編集
                        </button>

                        <button
                            wire:click="delete({{ $event->id }})"
                            wire:confirm="この出来事を削除してもよろしいですか？"
                            class="text-xs font-semibold px-3 py-1 rounded-lg border border-[#fa5246] text-[#fa5246] hover:bg-[#fa5246]/10 transition">
                            削除
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-sm text-[#475d5b] bg-white border border-dashed border-[#00332c]/20 rounded-xl p-6 text-center">
                    まだ出来事が登録されていません。<br class="hidden md:block" />
                    上のフォームから最初の一件を追加してみてください。
                </div>
            @endforelse
        </div>
    </div>

    {{-- モチベーショングラフ表示エリア --}}
    <div 
        x-show="showGraph" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="max-w-4xl mx-auto mt-8 mb-8"
        style="display: none;"
    >
        <div class="bg-white rounded-2xl shadow-md border border-[#00332c]/10 p-6">
            <h3 class="text-lg font-semibold text-[#00473e] mb-4">モチベーショングラフ</h3>
            
            @if($events->count() > 0)
                <div class="relative" style="height: 400px;">
                    <canvas id="motivationChart"></canvas>
                </div>
            @else
                <div class="text-center py-12 text-[#475d5b]">
                    <p class="text-sm">出来事を登録すると、グラフが表示されます。</p>
                </div>
            @endif
        </div>
    </div>

    {{-- 下部ボタンエリア --}}
    <div class="max-w-4xl mx-auto mt-8 pb-8">
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
                @click="showGraph = !showGraph; showTimeline = false"
                class="flex items-center justify-center gap-2 bg-[#faae2b] text-[#00473e] font-semibold text-sm rounded-xl px-6 py-3 shadow hover:opacity-90 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                モチベーショングラフ表示
            </button>

            <button
                @click="showTimeline = !showTimeline; showGraph = false"
                class="flex items-center justify-center gap-2 bg-white text-[#00473e] font-semibold text-sm rounded-xl px-6 py-3 shadow border border-[#faae2b] hover:bg-[#fff9eb] transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                人生史一覧表示
            </button>
        </div>
    </div>
</div>

@if($events->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const events = @json($events);
    
    if (events.length === 0) return;
    
    // データを準備
    const labels = events.map(e => e.year);
    const motivations = events.map(e => e.motivation);
    const titles = events.map(e => e.title);
    
    // グラフの設定
    const ctx = document.getElementById('motivationChart');
    if (!ctx) return;
    
    let chartInstance = null;
    
    // Alpine.jsの表示状態を監視してグラフを初期化
    function initChart() {
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'モチベーション',
                    data: motivations,
                    borderColor: '#faae2b',
                    backgroundColor: 'rgba(250, 174, 43, 0.1)',
                    borderWidth: 2,
                    pointRadius: 6,
                    pointBackgroundColor: '#faae2b',
                    pointBorderColor: '#00473e',
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
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return titles[index] ? '「' + titles[index] + '」' : '';
                            }
                        },
                        backgroundColor: 'rgba(0, 71, 62, 0.9)',
                        titleColor: '#faae2b',
                        bodyColor: '#f2f7f5',
                        borderColor: '#faae2b',
                        borderWidth: 1,
                        padding: 12,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        min: 0,
                        ticks: {
                            stepSize: 20,
                            color: '#475d5b',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 71, 62, 0.1)'
                        },
                        title: {
                            display: true,
                            text: 'モチベーション',
                            color: '#00473e',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            color: '#475d5b',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: '西暦（年）',
                            color: '#00473e',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Alpine.jsの表示状態変更を監視
    const graphElement = document.querySelector('[x-show="showGraph"]');
    if (graphElement) {
        // 表示状態の監視（Alpine.jsの状態変更を検出）
        const observer = new MutationObserver(() => {
            const isVisible = graphElement.offsetParent !== null;
            if (isVisible && !chartInstance) {
                setTimeout(initChart, 100);
            } else if (!isVisible && chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
        });
        
        // Alpine.jsがDOMを変更するのを待つ
        setTimeout(() => {
            observer.observe(graphElement, {
                attributes: true,
                attributeFilter: ['style'],
                childList: false,
                subtree: false
            });
        }, 500);
        
        // Livewireの更新後に再チェック
        document.addEventListener('livewire:update', () => {
            setTimeout(() => {
                if (graphElement.offsetParent !== null && !chartInstance) {
                    initChart();
                }
            }, 100);
        });
    }
});
</script>
@endif


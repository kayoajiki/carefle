<div class="min-h-screen w-full bg-[#F0F7FF] text-[#1E3A5F] content-padding section-spacing-sm">
    {{-- ヘッダー --}}
    <div class="max-w-6xl mx-auto mb-12">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
            <div>
                <h1 class="heading-2 mb-4">
                    人生史一覧
                </h1>
                <p class="body-large">
                    これまでの人生の出来事を時系列で振り返ります。
                </p>
            </div>
            <a
                href="{{ route('life-history') }}"
                class="btn-secondary text-sm">
                編集に戻る
            </a>
        </div>
    </div>

    @if($events->count() > 0)
        <div class="max-w-6xl mx-auto">
            <div class="flex gap-8">
                {{-- 左側：タイムライン --}}
                <div class="hidden md:block w-16 flex-shrink-0">
                    <div class="relative">
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
                                            {{-- ラベル編集（縦書き） --}}
                                            <div class="absolute inset-0 flex items-center justify-center p-1">
                                                <div x-data="{ editing:false, localLabel: @js($event->timeline_label) }" class="w-full h-full">
                                                    <template x-if="!editing">
                                                        <button type="button"
                                                            class="w-full h-full text-[#2E5C8A] text-xs font-semibold"
                                                            style="writing-mode: vertical-rl; text-orientation: mixed;"
                                                            @click="editing = true; $nextTick(() => $refs.inp.focus())"
                                                        >
                                                            <span x-text="localLabel || '＋'" class="opacity-80"></span>
                                                        </button>
                                                    </template>
                                                    <template x-if="editing">
                                                        <input
                                                            x-ref="inp"
                                                            type="text"
                                                            maxlength="32"
                                                            class="w-full h-full bg-white/80 text-[#2E5C8A] text-xs text-center rounded outline-none"
                                                            style="writing-mode: vertical-rl; text-orientation: mixed;"
                                                            x-model.trim="localLabel"
                                                            @blur="editing=false; $wire.updateLabel({{ $event->id }}, localLabel)"
                                                            @keydown.enter.prevent="$event.target.blur()"
                                                        />
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- カラーパレット（左側） --}}
                                        <div class="absolute -left-8 top-0 h-full flex items-start">
                                            <div class="flex flex-col gap-1 mt-1">
                                                @php
                                                    // パステル系5色 + 白（計6色）
                                                    $palette = ['#FFFFFF', '#FFE4E6', '#FFD8A8', '#CDEAFE', '#D3F9D8', '#EBDCFB'];
                                                @endphp
                                                @foreach($palette as $pcolor)
                                                    <button type="button"
                                                            class="w-3.5 h-3.5 rounded-full border border-[#e5e7eb]"
                                                            style="background: {{ $pcolor }};"
                                                            onclick="setTimelineSegmentColor('event-{{ $year }}-{{ $event->id }}','{{ $pcolor }}')"
                                                            wire:click="updateColor({{ $event->id }}, '{{ $pcolor }}')"
                                                            title="{{ $pcolor }}"
                                                    ></button>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        {{-- セグメント間の矢印（最後の出来事以外） --}}
                                        @if(!($loop->parent->last && $loop->last))
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full w-0 h-0 border-l-[10px] border-r-[10px] border-t-[16px] border-transparent" style="border-top-color: #9ca3af; z-index: 10;"></div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 右側：出来事一覧 --}}
                <div class="flex-1 min-w-0">
                    <div class="space-y-4">
                        @foreach($years as $year)
                            @php
                                $yearEvents = $eventsByYear[$year];
                            @endphp
                            
                            @foreach($yearEvents as $event)
                                <div id="event-{{ $year }}-{{ $event->id }}" class="card-refined p-6 md:p-8">
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
                    まだ出来事が登録されていません。<br>
                    <a href="{{ route('life-history') }}" class="text-[#2E5C8A] underline hover:text-[#6BB6FF] transition">
                        人生史の作成
                    </a>ページから最初の出来事を追加してみてください。
                </p>
            </div>
        </div>
    @endif
</div>

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

// 色変更用ヘルパー（非永続）
window.setTimelineSegmentColor = function(eventId, color) {
    const seg = document.querySelector('[data-event-id="' + eventId + '"]');
    if (seg) {
        seg.style.backgroundColor = color;
        seg.style.borderColor = '#e5e7eb';
    }
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

// Livewireの各種イベント
document.addEventListener('livewire:init', function() {
    setTimeout(syncTimelineSegments, 100);
});

document.addEventListener('livewire:load', function() {
    setTimeout(syncTimelineSegments, 100);
});

document.addEventListener('livewire:update', function() {
    setTimeout(syncTimelineSegments, 100);
});

document.addEventListener('livewire:navigated', function() {
    setTimeout(syncTimelineSegments, 100);
});

// MutationObserverでDOMの変化を監視
const observer = new MutationObserver(function(mutations) {
    let shouldSync = false;
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' || mutation.type === 'attributes') {
            shouldSync = true;
        }
    });
    if (shouldSync) {
        setTimeout(syncTimelineSegments, 50);
    }
});

// オブザーバーを開始（Livewireのコンテンツがレンダリングされた後）
setTimeout(function() {
    const timelineContainer = document.querySelector('.timeline-segment')?.closest('.relative');
    if (timelineContainer) {
        observer.observe(timelineContainer.parentElement, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }
}, 500);
</script>

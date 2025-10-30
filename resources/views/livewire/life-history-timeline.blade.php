<div class="min-h-screen w-full bg-[#f2f7f5] text-[#475d5b] px-4 py-6 md:px-8">
    {{-- ヘッダー --}}
    <div class="max-w-6xl mx-auto mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-semibold text-[#00473e]">
                    人生史一覧
                </h1>
                <p class="text-sm text-[#475d5b] leading-relaxed mt-2">
                    これまでの人生の出来事を時系列で振り返ります。
                </p>
            </div>
            <a
                href="{{ route('life-history') }}"
                class="text-xs font-semibold px-4 py-2 rounded-lg border border-[#00473e]/20 text-[#00473e] bg-white hover:bg-[#f2f7f5] transition">
                編集に戻る
            </a>
        </div>
    </div>

    @if($events->count() > 0)
        <div class="max-w-6xl mx-auto">
            <div class="flex gap-8">
                {{-- 左側：タイムライン --}}
                <div class="hidden md:block w-40 flex-shrink-0">
                    <div class="relative pt-4">
                        {{-- タイムライン --}}
                        <div class="relative">
                            @php
                                $yearColors = [
                                    '#fa5246', '#faae2b', '#ffa8ba', '#faae2b',
                                    '#4ecdc4', '#95e1d3', '#a8d8ea', '#ffd3a5',
                                    '#ffaaa5', '#ff8b94', '#c44569', '#f8b500'
                                ];
                                $colorIndex = 0;
                            @endphp
                            
                            @foreach($years as $index => $year)
                                @php
                                    $yearEvents = $eventsByYear[$year];
                                    $segmentHeight = max(100, 80 + ($yearEvents->count() * 50));
                                    $color = $yearColors[$colorIndex % count($yearColors)];
                                    $colorIndex++;
                                @endphp
                                
                                <div class="relative mb-2">
                                    {{-- 年号ラベル --}}
                                    <div class="absolute -left-20 top-1/2 -translate-y-1/2 text-sm font-bold text-[#00473e] whitespace-nowrap">
                                        {{ $year }}
                                    </div>
                                    
                                    {{-- カラフルなセグメント --}}
                                    <div 
                                        class="w-16 rounded-lg relative shadow-md"
                                        style="background: {{ $color }}; height: {{ $segmentHeight }}px; min-height: 100px;"
                                    >
                                        {{-- セグメント間の矢印（最後以外） --}}
                                        @if(!$loop->last)
                                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full w-0 h-0 border-l-[10px] border-r-[10px] border-t-[16px] border-transparent" style="border-top-color: #9ca3af;"></div>
                                        @endif
                                    </div>
                                </div>
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
                                <div class="bg-white rounded-xl border border-[#00332c]/10 shadow-sm p-4 md:p-6">
                                    {{-- ヘッダー --}}
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-[#00473e] bg-[#f2f7f5] px-2 py-1 rounded">
                                                {{ $event->year }}年
                                            </span>
                                        </div>
                                    </div>

                                    {{-- タイトル --}}
                                    <h3 class="text-base font-semibold text-[#00473e] mb-2">
                                        {{ $event->title }}
                                    </h3>

                                    {{-- 内容 --}}
                                    @if($event->description)
                                        <div class="text-sm text-[#475d5b] leading-relaxed mt-2 whitespace-pre-line">
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
            <div class="bg-white rounded-xl border border-dashed border-[#00332c]/20 p-12 text-center">
                <p class="text-sm text-[#475d5b]">
                    まだ出来事が登録されていません。<br>
                    <a href="{{ route('life-history') }}" class="text-[#00473e] underline hover:text-[#faae2b] transition">
                        人生史の作成
                    </a>ページから最初の出来事を追加してみてください。
                </p>
            </div>
        </div>
    @endif
</div>

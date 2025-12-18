<div>
@if(!$isUnlocked)
    {{-- アンロックされていない場合は非表示 --}}
@elseif($progress)
    {{-- マッピング進捗バーを表示 --}}
    <div class="mb-6">
        <div class="card-refined surface-blue p-6">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="heading-3 text-[#2E5C8A]">次のステップ</h3>
                    <span class="body-small text-[#1E3A5F]/60">{{ $progressPercentage }}%</span>
                </div>
                {{-- 進捗バー --}}
                <div class="w-full bg-[#E8F4FF] rounded-full h-2 overflow-hidden">
                    <div 
                        class="h-2 bg-[#6BB6FF] transition-all duration-500"
                        style="width: {{ $progressPercentage }}%"
                    ></div>
                </div>
            </div>

            {{-- ステップ一覧（オンボーディングと同じスタイル） --}}
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                @foreach($itemStatuses as $itemKey => $item)
                    <a 
                        href="{{ $item['route'] }}" 
                        wire:navigate
                        class="flex flex-col items-center gap-2 p-3 rounded-lg transition-all {{ $item['completed'] ? 'bg-[#6BB6FF]/10 border-2 border-[#6BB6FF]' : ($item['isCurrent'] ? 'bg-[#E8F4FF] border-2 border-[#6BB6FF]/50' : 'bg-white/50 border border-[#6BB6FF]/20') }}"
                    >
                        @if($item['completed'])
                            <div class="w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full {{ $item['isCurrent'] ? 'bg-[#6BB6FF]/20' : 'bg-[#E8F4FF]' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $item['isCurrent'] ? 'text-[#6BB6FF]' : 'text-[#1E3A5F]/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($itemKey === 'life_history')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @elseif($itemKey === 'current_diaries')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    @elseif($itemKey === 'strengths_report')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    @elseif($itemKey === 'wcm_sheet')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    @elseif($itemKey === 'milestones')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                    @elseif($itemKey === 'my_goal')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    @endif
                                </svg>
                            </div>
                        @endif
                        <span class="text-xs font-medium {{ $item['completed'] ? 'text-[#2E5C8A]' : ($item['isCurrent'] ? 'text-[#6BB6FF]' : 'text-[#1E3A5F]/40') }}">
                            {{ $item['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- 次のアクション --}}
            @if($nextItem)
            <div class="mt-4 p-3 bg-[#E8F4FF] rounded-lg border border-[#6BB6FF]/20">
                <p class="body-small text-[#1E3A5F]/70 text-center">
                    次のステップ: {{ $itemStatuses[$nextItem]['label'] ?? $nextItem }}
                </p>
            </div>
            @else
            <div class="mt-4 p-3 bg-[#6BB6FF]/10 rounded-lg border border-[#6BB6FF] text-center">
                <p class="body-text text-[#2E5C8A] font-medium">
                    🎉 曼荼羅マッピングが完成しました！
                </p>
            </div>
            @endif
        </div>
    </div>
@endif
</div>

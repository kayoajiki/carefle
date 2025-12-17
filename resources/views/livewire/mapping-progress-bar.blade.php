<div>
@if(!$isUnlocked)
    {{-- アンロックされていない場合は非表示 --}}
@elseif($progress)
    {{-- マッピング進捗バーを表示 --}}
    <div class="mb-6">
        <div class="card-refined surface-blue p-6">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="heading-3 text-[#2E5C8A]">曼荼羅マッピング進捗</h3>
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

            {{-- セクション別進捗 --}}
            <div class="space-y-4">
                @foreach($sectionProgresses as $sectionKey => $sectionProgress)
                <div class="border border-[#6BB6FF]/20 rounded-lg p-4 bg-white/50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="heading-4 text-lg text-[#2E5C8A]">
                            {{ $sectionProgress['sectionLabel'] }}
                        </h4>
                        <span class="body-small text-[#1E3A5F]/60">
                            {{ $sectionProgress['completedCount'] }}/{{ $sectionProgress['totalCount'] }}
                        </span>
                    </div>
                    
                    {{-- セクション内の項目 --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach($sectionProgress['items'] as $item)
                        <div class="flex items-center gap-2 p-2 rounded {{ $item['completed'] ? 'bg-[#6BB6FF]/10' : 'bg-[#E8F4FF]/50' }}">
                            @if($item['completed'])
                                <div class="w-5 h-5 rounded-full bg-[#6BB6FF] flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-5 h-5 rounded-full {{ $item['canComplete'] ? 'bg-[#6BB6FF]/20' : 'bg-[#E8F4FF]' }} flex items-center justify-center flex-shrink-0">
                                    <div class="w-2 h-2 rounded-full {{ $item['canComplete'] ? 'bg-[#6BB6FF]' : 'bg-[#1E3A5F]/20' }}"></div>
                                </div>
                            @endif
                            <span class="text-xs font-medium {{ $item['completed'] ? 'text-[#2E5C8A]' : ($item['canComplete'] ? 'text-[#6BB6FF]' : 'text-[#1E3A5F]/40') }}">
                                {{ $item['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- 次のアクション --}}
            @if($nextItem)
            <div class="mt-4 p-3 bg-[#E8F4FF] rounded-lg border border-[#6BB6FF]/20">
                <p class="body-small text-[#1E3A5F]/70 text-center">
                    次のステップ: 
                    @php
                        $labels = [
                            'past_diagnosis' => '過去の診断',
                            'past_diaries' => '過去の日記',
                            'life_history' => '人生史',
                            'current_diagnosis' => '最新の診断',
                            'current_diaries' => '最近の日記',
                            'strengths_report' => '持ち味レポ',
                            'wcm_sheet' => 'WCMシート',
                            'milestones' => 'マイルストーン',
                        ];
                    @endphp
                    {{ $labels[$nextItem] ?? $nextItem }}
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

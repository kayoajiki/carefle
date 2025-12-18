<div>
@if(!$isUnlocked)
    {{-- アンロックされていない場合は非表示 --}}
@elseif($progress)
    {{-- マッピング進捗バーを表示 --}}
    <div class="mb-6">
        <div class="card-refined surface-blue p-4 sm:p-6">
            <div class="mb-3 sm:mb-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#2E5C8A]">次のステップ</h3>
                        <button
                            type="button"
                            onclick="document.getElementById('medal-explanation-modal').classList.remove('hidden')"
                            class="w-6 h-6 rounded-full bg-[#6BB6FF]/20 hover:bg-[#6BB6FF]/30 flex items-center justify-center transition-colors group"
                            title="メダルの説明"
                        >
                            <svg class="w-4.5 h-4.5 text-[#6BB6FF] group-hover:text-[#2E5C8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>
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
                    <div class="group relative">
                        <a 
                            href="{{ $item['route'] }}" 
                            wire:navigate
                            class="flex flex-col items-center gap-2 p-3 rounded-lg transition-all {{ $item['completed'] ? 'bg-[#6BB6FF]/10 border-2 border-[#6BB6FF]' : ($item['isCurrent'] ? 'bg-[#E8F4FF] border-2 border-[#6BB6FF]/50' : 'bg-white/50 border border-[#6BB6FF]/20') }}"
                        >
                        @php
                            $medalLevel = $item['medalLevel'] ?? 'none';
                            $medalColors = [
                                'bronze' => '#CD7F32',
                                'silver' => '#C0C0C0',
                                'gold' => '#FFD700',
                                'platinum' => '#E5E4E2',
                            ];
                            $medalColor = $medalColors[$medalLevel] ?? '#E8F4FF';
                            $hasMedal = $medalLevel !== 'none';
                        @endphp
                        @if($hasMedal)
                            {{-- メダルアイコン --}}
                            <div class="relative">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, {{ $medalColor }}, {{ $medalColor }}dd); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    @if($medalLevel === 'bronze')
                                        <span class="text-lg">🥉</span>
                                    @elseif($medalLevel === 'silver')
                                        <span class="text-lg">🥈</span>
                                    @elseif($medalLevel === 'gold')
                                        <span class="text-lg">🥇</span>
                                    @elseif($medalLevel === 'platinum')
                                        <span class="text-lg">💎</span>
                                    @endif
                                </div>
                                @if($item['hasReviewAlert'] ?? false)
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center border-2 border-white">
                                        <span class="text-xs">🔔</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- 未完了の場合は従来のアイコン --}}
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
                        
                        {{-- ツールチップ --}}
                        @if(!empty($item['medalDescription']))
                        <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-64 p-3 bg-[#2E5C8A] text-white text-xs rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none">
                            <div class="space-y-2">
                                <div class="font-semibold mb-2 border-b border-white/20 pb-2">{{ $item['label'] }}</div>
                                
                                <div>
                                    <div class="font-medium mb-1">メダルレベル基準:</div>
                                    <ul class="space-y-1 ml-2">
                                        <li>🥉 銅: {{ $item['medalDescription']['bronze'] ?? '' }}</li>
                                        <li>🥈 銀: {{ $item['medalDescription']['silver'] ?? '' }}</li>
                                        <li>🥇 金: {{ $item['medalDescription']['gold'] ?? '' }}</li>
                                        <li>💎 プラチナ: {{ $item['medalDescription']['platinum'] ?? '' }}</li>
                                    </ul>
                                </div>
                                
                                <div class="pt-2 border-t border-white/20 mt-2">
                                    <div class="text-[#E8F4FF] text-xs">{{ $item['medalDescription']['alert'] ?? '' }}</div>
                                </div>
                            </div>
                            
                            {{-- ツールチップの矢印 --}}
                            <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-[#2E5C8A]"></div>
                        </div>
                        @endif
                    </div>
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
                @if($topicMessage ?? null)
                    <p class="body-text text-[#2E5C8A] font-medium">
                        {{ $topicMessage }}
                    </p>
                @else
                    <p class="body-text text-[#2E5C8A] font-medium">
                        🎉 すべてのステップが完了しました！
                    </p>
                @endif
            </div>
            @endif
        </div>
    </div>
@endif

{{-- メダル説明モーダル --}}
<div id="medal-explanation-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="event.target.id === 'medal-explanation-modal' && event.target.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="heading-3 text-[#2E5C8A]">メダルの説明</h3>
            <button
                type="button"
                onclick="document.getElementById('medal-explanation-modal').classList.add('hidden')"
                class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors"
            >
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <span class="text-2xl">🥉</span>
                <div>
                    <p class="body-text font-semibold text-[#2E5C8A] mb-1">銅メダル</p>
                    <p class="body-small text-[#1E3A5F]/70">各項目の最初の達成レベルです</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <span class="text-2xl">🥈</span>
                <div>
                    <p class="body-text font-semibold text-[#2E5C8A] mb-1">銀メダル</p>
                    <p class="body-small text-[#1E3A5F]/70">より多くのデータを蓄積した証です</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <span class="text-2xl">🥇</span>
                <div>
                    <p class="body-text font-semibold text-[#2E5C8A] mb-1">金メダル</p>
                    <p class="body-small text-[#1E3A5F]/70">継続的な取り組みの成果です</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <span class="text-2xl">💎</span>
                <div>
                    <p class="body-text font-semibold text-[#2E5C8A] mb-1">プラチナメダル</p>
                    <p class="body-small text-[#1E3A5F]/70">最高レベルの達成を表します</p>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-start gap-3">
                    <span class="text-lg">🔔</span>
                    <div>
                        <p class="body-text font-semibold text-[#2E5C8A] mb-1">見直しアラート</p>
                        <p class="body-small text-[#1E3A5F]/70">最終更新から一定期間以上経過している場合に表示されます。定期的に見直すことで、より良い結果が得られます。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

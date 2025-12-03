<div class="card-refined p-8">
    <h2 class="heading-2 text-2xl text-[#2E5C8A] mb-6">成長実感の可視化</h2>

    {{-- 期間選択 --}}
    <div class="mb-6">
        <label class="body-small font-medium text-[#2E5C8A] mb-2 block">比較期間</label>
        <div class="flex gap-2">
            <button
                wire:click="$set('comparisonPeriod', 'week')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $comparisonPeriod === 'week' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                1週間前
            </button>
            <button
                wire:click="$set('comparisonPeriod', 'month')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $comparisonPeriod === 'month' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                1ヶ月前
            </button>
            <button
                wire:click="$set('comparisonPeriod', 'quarter')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $comparisonPeriod === 'quarter' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                3ヶ月前
            </button>
            <button
                wire:click="$set('comparisonPeriod', 'year')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $comparisonPeriod === 'year' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                1年前
            </button>
        </div>
    </div>

    @if($isLoading)
        <div class="flex items-center justify-center py-12">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>
            <span class="body-small text-[#1E3A5F]/60 ml-3">分析中...</span>
        </div>
    @elseif($growthData)
        <div class="space-y-6">
            {{-- モチベーション推移 --}}
            <div class="bg-[#E8F4FF] rounded-xl p-6">
                <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">モチベーションの推移</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">過去の平均</p>
                        <p class="heading-3 text-2xl font-bold text-[#2E5C8A]">{{ $growthData['motivation']['past'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">最近の平均</p>
                        <p class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $growthData['motivation']['recent'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">変化</p>
                        <p class="heading-3 text-2xl font-bold {{ $growthData['motivation']['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $growthData['motivation']['change'] >= 0 ? '+' : '' }}{{ $growthData['motivation']['change'] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- 内省頻度 --}}
            <div class="bg-white border-2 border-[#6BB6FF] rounded-xl p-6">
                <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">内省の頻度</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">過去の期間</p>
                        <p class="heading-3 text-2xl font-bold text-[#2E5C8A]">{{ $growthData['frequency']['past'] }}回</p>
                    </div>
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">最近の期間</p>
                        <p class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $growthData['frequency']['recent'] }}回</p>
                    </div>
                    <div class="text-center">
                        <p class="body-small text-[#1E3A5F]/60 mb-1">変化</p>
                        <p class="heading-3 text-2xl font-bold {{ $growthData['frequency']['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $growthData['frequency']['change'] >= 0 ? '+' : '' }}{{ $growthData['frequency']['change'] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- AI成長分析 --}}
            @if($growthData['analysis'])
                <div class="bg-[#E8F4FF] rounded-xl p-6">
                    <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">成長分析</h3>
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <p class="body-text text-[#1E3A5F] whitespace-pre-wrap flex-1">{{ $growthData['analysis'] }}</p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <p class="body-text text-[#1E3A5F]/60">成長データを読み込めませんでした。</p>
        </div>
    @endif
</div>


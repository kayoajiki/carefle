<div class="card-refined p-8">
    <div class="mb-6">
        <h2 class="heading-2 text-2xl text-[#2E5C8A] mb-4">振り返りサマリー</h2>
        
        {{-- タイプ切り替え --}}
        <div class="flex gap-2 mb-6">
            <button
                wire:click="switchType('weekly')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $reflectionType === 'weekly' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                週次
            </button>
            <button
                wire:click="switchType('monthly')"
                class="px-4 py-2 rounded-lg body-text font-medium transition-colors {{ $reflectionType === 'monthly' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                月次
            </button>
        </div>

        {{-- 期間選択 --}}
        <div class="mb-6">
            @if($reflectionType === 'weekly')
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">週の開始日</label>
                <input
                    type="date"
                    wire:model="selectedWeekStart"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                />
            @else
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">月の開始日</label>
                <input
                    type="date"
                    wire:model="selectedMonthStart"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                />
            @endif
        </div>

        {{-- 生成ボタン --}}
        <button
            wire:click="generateSummary"
            :disabled="$isLoading"
            class="px-6 py-3 bg-[#6BB6FF] text-white body-text font-medium rounded-xl hover:bg-[#5AA5E6] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
            @if($isLoading)
                <span class="flex items-center gap-2">
                    <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    生成中...
                </span>
            @else
                {{ $reflectionType === 'weekly' ? '週次' : '月次' }}サマリーを生成
            @endif
        </button>
    </div>

    @if($error)
        <div class="bg-red-50 border border-red-200 text-red-800 body-small p-4 rounded-lg mb-4">
            {{ $error }}
        </div>
    @endif

    @if($summary)
        <div class="space-y-6">
            {{-- サマリー --}}
            <div class="bg-[#E8F4FF] rounded-xl p-6">
                <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">
                    {{ $summary['period'] }}サマリー（{{ $summary['start_date'] }}〜{{ $summary['end_date'] }}）
                </h3>
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $summary['summary'] }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-[#2E5C8A]/20">
                    <p class="body-small text-[#1E3A5F]/60">
                        日記数: {{ $summary['diary_count'] }}件 | 
                        平均モチベーション: {{ $summary['avg_motivation'] }}/100
                    </p>
                </div>
            </div>

            {{-- 気づき --}}
            @if(!empty($summary['insights']))
                <div class="bg-white border-2 border-[#6BB6FF] rounded-xl p-6">
                    <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">主な気づき</h3>
                    <ul class="space-y-3">
                        @foreach($summary['insights'] as $insight)
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#6BB6FF] text-white text-xs flex items-center justify-center mt-0.5">
                                    {{ $loop->iteration }}
                                </span>
                                <p class="body-text text-[#1E3A5F] flex-1">{{ $insight }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>


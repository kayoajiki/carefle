@if($isComplete && ($showCompletionMessage ?? false))
    {{-- オンボーディング完了時は達成バッジのみ表示（1週間経過後は非表示） --}}
    <div class="mb-6">
        <div class="card-refined surface-blue p-2 sm:p-3 text-center">
            <p class="body-text text-[#2E5C8A] font-semibold">🎉 オンボーディング完了</p>
        </div>
    </div>
@elseif($progress)
    {{-- オンボーディング未完了時は進捗バーを表示 --}}
    <div class="mb-6">
        <div class="card-refined surface-blue p-6">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex flex-col">
                        <h3 class="heading-3 text-[#2E5C8A]">スタートガイド</h3>
                        <p class="text-sm sm:text-base text-[#2E5C8A] font-medium mt-1">7日間の内省で自分の持ち味を発見しよう</p>
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

            {{-- ステップ一覧 --}}
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                @foreach($stepStatuses as $stepKey => $step)
                    <a 
                        href="{{ route($step['route']) }}" 
                        wire:navigate
                        class="flex flex-col items-center gap-2 p-3 rounded-lg transition-all {{ $step['completed'] ? 'bg-[#6BB6FF]/10 border-2 border-[#6BB6FF]' : ($step['isCurrent'] ? 'bg-[#E8F4FF] border-2 border-[#6BB6FF]/50' : 'bg-white/50 border border-[#6BB6FF]/20') }}"
                    >
                        @if($step['completed'])
                            <div class="w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full {{ $step['isCurrent'] ? 'bg-[#6BB6FF]/20' : 'bg-[#E8F4FF]' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $step['isCurrent'] ? 'text-[#6BB6FF]' : 'text-[#1E3A5F]/40' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($step['icon'] === 'chart-bar')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    @elseif($step['icon'] === 'document-text')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    @elseif($step['icon'] === 'user-circle')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 16h8a4 4 0 012.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @elseif($step['icon'] === 'light-bulb')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    @elseif($step['icon'] === 'calendar')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    @elseif($step['icon'] === 'book-open')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    @endif
                                </svg>
                            </div>
                        @endif
                        <span class="text-xs font-medium {{ $step['completed'] ? 'text-[#2E5C8A]' : ($step['isCurrent'] ? 'text-[#6BB6FF]' : 'text-[#1E3A5F]/40') }}">
                            {{ $step['label'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="content-padding section-spacing-sm">
    <div class="w-full max-w-6xl mx-auto">
        <!-- ヘッダー -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="heading-2 mb-2">日記カレンダー</h1>
                    <p class="body-large">日付をクリックして日記を書いたり、編集したりできます。</p>
                </div>
                <a 
                    href="{{ route('diary.chat') }}" 
                    class="btn-primary flex items-center gap-2"
                    wire:navigate
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    チャット形式で内省
                </a>
            </div>
        </div>

        @if(session('message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
                {{ session('message') }}
            </div>
        @endif

        <!-- カレンダー -->
        <div class="card-refined p-4 md:p-8 mb-8">
            <!-- 月の切り替え -->
            <div class="flex items-center justify-between mb-4 md:mb-8">
                <button
                    wire:click="previousMonth"
                    class="btn-secondary text-xs md:text-sm px-2 md:px-4"
                >
                    ← 前月
                </button>
                <h2 class="heading-3 text-lg md:text-2xl">{{ $monthName }}</h2>
                <button
                    wire:click="nextMonth"
                    class="btn-secondary text-xs md:text-sm px-2 md:px-4"
                >
                    次月 →
                </button>
            </div>

            <!-- カレンダーグリッド -->
            <div class="grid grid-cols-7 gap-1 md:gap-2">
                <!-- 曜日ヘッダー -->
                @foreach(['日', '月', '火', '水', '木', '金', '土'] as $day)
                    <div class="text-center text-xs md:text-sm font-semibold text-[#2E5C8A] py-1 md:py-2">
                        {{ $day }}
                    </div>
                @endforeach

                <!-- 日付 -->
                @foreach($days as $day)
                    @php
                        $hasDiary = isset($diaries[$day['date']]);
                        $diary = $hasDiary ? $diaries[$day['date']] : null;
                        $hasPhoto = $diary && $diary->photo;
                    @endphp
                    <button
                        wire:click="selectDate('{{ $day['date'] }}')"
                        class="aspect-square rounded-lg md:rounded-xl border-2 transition-all hover:scale-105 relative overflow-hidden min-h-[2.5rem] md:min-h-0
                            {{ $day['isCurrentMonth'] ? '' : 'opacity-30' }}
                            {{ $day['isToday'] ? 'border-[#6BB6FF] bg-[#6BB6FF]/10' : ($hasDiary ? 'border-[#6BB6FF]' : 'border-[#2E5C8A]/20') }}
                            {{ $hasDiary ? 'bg-[#E8F4FF]' : 'bg-white' }}
                            {{ $selectedDate === $day['date'] ? 'ring-2 ring-[#6BB6FF] ring-offset-2' : '' }}
                        "
                    >
                        <!-- 日付（左上） -->
                        <div class="absolute top-1 left-1 md:top-2 md:left-2 z-10">
                            <span class="text-xs md:text-sm font-semibold {{ $hasDiary ? 'text-[#2E5C8A]' : 'text-[#1E3A5F]' }}">
                                {{ $day['day'] }}
                            </span>
                        </div>
                        
                        <!-- 写真のサムネイルまたはアイコン -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            @if($hasPhoto)
                                <img 
                                    src="{{ asset('storage/' . $diary->photo) }}" 
                                    alt="日記の写真" 
                                    class="w-full h-full object-cover rounded-lg md:rounded-xl"
                                    loading="lazy"
                                />
                            @elseif($hasDiary)
                                <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-[#6BB6FF] shadow-sm"></div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 日記フォーム（モーダル風） -->
        @if($showForm)
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click="closeForm">
                <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="p-6 border-b border-[#2E5C8A]/10 flex items-center justify-between">
                        <h3 class="heading-3 text-xl">
                            @if($selectedDate)
                                {{ \Carbon\Carbon::parse($selectedDate)->format('Y年n月j日') }}の日記
                            @endif
                        </h3>
                        <button
                            wire:click="closeForm"
                            class="text-[#1E3A5F] hover:text-[#2E5C8A] transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6" wire:key="diary-form-{{ $selectedDate }}">
                        <div>
                            @livewire('diary-form', ['date' => $selectedDate, 'diaryId' => $selectedDiaryId], key('diary-form-' . $selectedDate . '-' . ($selectedDiaryId ?? 'new')))
                        </div>
                        @if($selectedDiaryId)
                            <div>
                                @livewire('diary-reflection-feedback', ['diaryId' => $selectedDiaryId], key('diary-feedback-' . $selectedDiaryId))
                            </div>
                        @endif
                    </div>
                    @if($selectedDiary)
                        <div class="p-6 border-t border-[#2E5C8A]/10">
                            <button
                                wire:click="deleteDiary({{ $selectedDiary->id }})"
                                onclick="return confirm('この日記を削除しますか？')"
                                class="btn-secondary text-sm border-red-400 text-red-600 hover:bg-red-50"
                            >
                                削除
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
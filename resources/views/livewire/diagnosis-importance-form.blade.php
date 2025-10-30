<div class="min-h-screen bg-[#f2f7f5] px-4 py-8">
    <div class="w-full max-w-3xl mx-auto bg-white rounded-2xl shadow-md p-8">
        @php($q = $questions[$currentIndex] ?? null)
        <div class="mb-4 text-xs text-[#475d5b]">重要度チェック {{ $currentIndex+1 }}/{{ count($questions) }}</div>
        <h2 class="text-lg md:text-xl font-semibold text-[#00473e] leading-relaxed">
            {{ $q ? ($displayTexts[$q['question_id']] ?? '') : '' }}
        </h2>
        <div class="mt-4 flex flex-col gap-3">
            @if($q)
                @foreach($importanceOptions as $opt)
                    <button type="button"
                            class="w-full border rounded-lg px-4 py-3 text-left text-sm font-medium border-[#00473e]/20 data-[active=true]:bg-[#dbeafe] data-[active=true]:border-[#60a5fa]"
                            x-data
                            :data-active="@js($answers[$q['question_id']] ?? null) === {{ $opt['value'] }}"
                            wire:click="selectOption('{{ $q['question_id'] }}', {{ $opt['value'] }})">
                        <div class="text-[#00473e] text-sm font-semibold">{{ $opt['label'] }}</div>
                    </button>
                @endforeach
            @endif
        </div>
        <div class="mt-6 flex items-center justify-between">
            <button class="text-xs px-4 py-2 rounded-md border border-[#00473e]/30 text-[#00473e] bg-white font-medium" wire:click="prev" @disabled($currentIndex===0)>戻る</button>
            <button class="text-xs px-4 py-2 rounded-md font-semibold bg-[#60a5fa] text-white shadow-sm" wire:click="next">次へ</button>
        </div>
    </div>
</div>



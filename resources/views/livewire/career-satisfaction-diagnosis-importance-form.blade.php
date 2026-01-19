<div class="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
    <div class="w-full max-w-3xl mx-auto card-refined p-8 md:p-10">
        @php($q = $questions[$currentIndex] ?? null)
        <div class="mb-6 body-small">
            重要度チェック（必須） {{ $currentIndex+1 }}/{{ count($questions) }}
        </div>
        <h2 class="heading-3 text-xl mb-8">
            {{ $q ? ($displayTexts[$q['id']] ?? '') : '' }}
        </h2>
        <div class="flex flex-col gap-4 mb-8">
            @if($q)
                @foreach($importanceOptions as $opt)
                    <button type="button"
                            class="w-full border-2 rounded-xl px-6 py-4 text-left transition-all duration-200 border-[#2E5C8A]/20 bg-white hover:border-[#6BB6FF]/50 hover:bg-[#F0F7FF] data-[active=true]:bg-[#E8F4FF] data-[active=true]:border-[#6BB6FF] data-[active=true]:shadow-sm"
                            x-data
                            :data-active="@js($answers[$q['id']] ?? null) === {{ $opt['value'] }}"
                            wire:click="selectOption('{{ $q['id'] }}', {{ $opt['value'] }})">
                        <div class="body-text font-semibold text-[#2E5C8A]">{{ $opt['label'] }}</div>
                    </button>
                @endforeach
            @endif
        </div>
        
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 text-xs p-3 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="flex items-center justify-between pt-6 border-t border-[#2E5C8A]/10">
            <button class="btn-secondary text-sm" wire:click="prev" @disabled($currentIndex===0)>戻る</button>
            <button class="btn-primary text-sm" wire:click="next">
                @if($currentIndex < count($questions) - 1)
                    次へ
                @else
                    結果を見る
                @endif
            </button>
        </div>
    </div>
</div>


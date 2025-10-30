<div class="min-h-screen bg-[#f2f7f5] px-4 py-8">
    <div class="w-full max-w-xl mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-[#00473e]">WCMシート作成（Step 1/2）</h1>
            <p class="text-sm text-[#475d5b] mt-2">バリュークエスチョンに答えていきましょう</p>
        </div>

        @php
            $map = [
                0=>'will',1=>'will',2=>'will',3=>'will',4=>'will',
                5=>'can',6=>'can',7=>'can',8=>'can',9=>'can',
                10=>'must',11=>'must',12=>'must',13=>'must',14=>'must'
            ];
            $indexInSection = fn($s) => $s%5;
            $section = $map[$step] ?? 'will';
            $label = strtoupper($section);
            $question = $questions[$section][$indexInSection($step)];
        @endphp

        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-6">
            <div>
                <div class="flex justify-between items-baseline mb-2">
                    <div class="text-xs font-medium text-[#00473e]">Q{{ $step+1 }}/15 <span class="ml-2">{{ $label }}</span></div>
                    <div class="text-[10px] text-[#475d5b]">約5〜10分</div>
                </div>
                <div class="w-full bg-[#f2f7f5] rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-[#faae2b]" style="width: {{ round((($step+1)/15)*100) }}%"></div>
                </div>
            </div>

            <div>
                <h2 class="text-base font-semibold text-[#00473e] leading-relaxed">{{ $question }}</h2>
                <textarea
                    wire:model.defer="answers.{{ $section }}.{{ $indexInSection($step) }}"
                    rows="6"
                    class="mt-3 w-full text-sm rounded-md border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e] p-3 focus:outline-none focus:ring-2 focus:ring-[#faae2b]"
                    placeholder="自由に記入してください"
                ></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[#00473e]/10">
                <button class="text-xs px-4 py-2 rounded-md border border-[#00473e]/30 text-[#00473e] bg-white font-medium" wire:click="prev" @disabled($step===0)>戻る</button>
                <button class="text-xs px-4 py-2 rounded-md font-semibold bg-[#faae2b] text-[#00473e] shadow-sm" wire:click="{{ $step<14 ? 'next' : 'finish' }}">
                    {{ $step<14 ? '次へ' : 'シートを生成' }}
                </button>
            </div>
        </div>
        @if (session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">{{ session('error') }}</div>
        @endif
    </div>
</div>



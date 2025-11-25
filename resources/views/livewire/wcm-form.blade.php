<div class="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
    <div class="w-full max-w-3xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="heading-2 mb-4">WCMシート作成（Step 1/2）</h1>
            <p class="body-large">ここでは綺麗にまとまっていなくても構いません。文章でなくても単語でも。思いのまま書いていきましょう。</p>
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
            $hint = $hints[$section][$indexInSection($step)] ?? null;
        @endphp

        <div class="card-refined p-8 md:p-10 flex flex-col gap-8" wire:key="card-{{ $step }}">
            <div>
                <div class="flex justify-between items-baseline mb-3">
                    <div class="body-small font-medium text-[#2E5C8A]">Q{{ $step+1 }}/15 <span class="ml-2">{{ $label }}</span></div>
                    <div class="body-small">約5〜10分</div>
                </div>
                <div class="w-full bg-[#F0F7FF] rounded-full h-3 overflow-hidden">
                    <div class="h-3 bg-[#6BB6FF] rounded-full transition-all duration-300" style="width: {{ round((($step+1)/15)*100) }}%"></div>
                </div>
            </div>

            <div>
                <h2 class="heading-3 text-xl mb-4">{{ $question }}</h2>
                @if($hint)
                    <p class="body-text mb-6">ヒント：{{ $hint }}</p>
                @endif
                <textarea
                    wire:key="ans-{{ $step }}"
                    wire:model.debounce.800ms="answersLinear.{{ $step }}"
                    rows="10"
                    class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    placeholder="自由に記入してください"
                ></textarea>
                @if($draftSavedAt)
                    <div class="mt-3 body-small">下書き保存: {{ $draftSavedAt }}</div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-[#2E5C8A]/10">
                <button class="btn-secondary text-sm" wire:click="prev" @disabled($step===0)>戻る</button>
                <button class="btn-primary text-sm" wire:click="{{ $step<14 ? 'next' : 'finish' }}">
                    {{ $step<14 ? '次へ' : 'シートを生成' }}
                </button>
            </div>
        </div>
        @if (session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">{{ session('error') }}</div>
        @endif
    </div>
</div>



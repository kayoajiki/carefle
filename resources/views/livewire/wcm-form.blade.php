<div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF] px-4 py-10">
    <div class="w-full max-w-3xl mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-semibold text-[#2E5C8A]">WCMシート作成（Step 1/2）</h1>
            <p class="text-base text-[#1E3A5F] mt-2">ここでは綺麗にまとまっていなくても構いません。文章でなくても単語でも、箇条書きでもOKです。思いついたことをそのまま書いてください。後でAI編集もできるので、気軽に記入しましょう。</p>
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
            
            // セクションごとのプレースホルダー
            $placeholders = [
                'will' => '例：3年後に起業している / 家族と充実した時間を過ごしている / 健康な体を維持している（単語でも箇条書きでもOK）',
                'can' => '例：プログラミングスキル / コミュニケーション能力 / 英語力（思いついたことをそのまま書いてください）',
                'must' => '例：毎月の収入を確保する / 家族との時間を守る / 健康診断を受ける（後でAI編集できるので、気軽に書いてください）',
            ];
            $placeholder = $placeholders[$section] ?? '自由に記入してください';
        @endphp

        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col gap-6" wire:key="card-{{ $step }}">
            <div>
                <div class="flex justify-between items-baseline mb-2">
                    <div class="text-sm font-medium text-[#2E5C8A]">Q{{ $step+1 }}/15 <span class="ml-2">{{ $label }}</span></div>
                    <div class="text-xs text-[#4A5A73]">約5〜10分</div>
                </div>
                <div class="w-full bg-[#E3ECF9] rounded-full h-3 overflow-hidden">
                    <div class="h-3 bg-gradient-to-r from-[#6BB6FF] to-[#2563EB]" style="width: {{ round((($step+1)/15)*100) }}%"></div>
                </div>
            </div>

            <div>
                <h2 class="text-lg md:text-xl font-semibold text-[#2E5C8A] leading-relaxed">{{ $question }}</h2>
                @if($hint)
                    <div class="mt-3 bg-[#E3ECF9] border-l-4 border-[#6BB6FF] p-3 rounded-r-md">
                        <p class="text-xs md:text-sm text-[#1E3A5F] leading-relaxed">
                            <span class="mr-1">💡</span><span class="font-medium">ヒント：</span>{{ $hint }}
                        </p>
                    </div>
                @endif
                
                <div class="mt-3 bg-[#E3ECF9] border border-[#6BB6FF]/30 rounded-md p-3">
                    <p class="text-sm text-[#1E3A5F] leading-relaxed">
                        <span class="mr-1">💡</span>思いついたことをそのまま書いてください。単語でも箇条書きでも大丈夫です。後でAI編集もできるので、完璧に書く必要はありません。
                    </p>
                </div>
                
                <textarea
                    wire:key="ans-{{ $step }}"
                    wire:model.debounce.800ms="answersLinear.{{ $step }}"
                    rows="6"
                    class="mt-3 w-full text-base rounded-md border border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#1E3A5F] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF]"
                    placeholder="{{ $placeholder }}"
                ></textarea>
                @if($draftSavedAt)
                    <div class="mt-2 text-xs text-[#4A5A73]">下書き保存: {{ $draftSavedAt }}</div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[#2E5C8A]/10">
                <button class="text-sm px-5 py-2.5 rounded-md border border-[#2E5C8A]/30 text-[#2E5C8A] bg-white font-medium hover:bg-[#F0F7FF] transition-colors" wire:click="prev" @disabled($step===0)>戻る</button>
                <button class="text-sm px-5 py-2.5 rounded-md font-semibold bg-[#6BB6FF] text-white hover:bg-[#5B8DCC] shadow-sm transition-colors" wire:click="{{ $step<14 ? 'next' : 'finish' }}">
                    {{ $step<14 ? '次へ' : 'シートを生成' }}
                </button>
            </div>
            
            <div class="pt-4 border-t border-[#2E5C8A]/10">
                <button
                    type="button"
                    wire:click="saveAndReturnToDashboard"
                    wire:loading.attr="disabled"
                    wire:target="saveAndReturnToDashboard"
                    class="w-full text-sm text-[#2E5C8A] hover:text-[#6BB6FF] transition-colors flex items-center gap-2 justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7 7-7M19 10v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span wire:loading.remove wire:target="saveAndReturnToDashboard">保存してダッシュボードに戻る</span>
                    <span wire:loading wire:target="saveAndReturnToDashboard" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        保存中...
                    </span>
                </button>
            </div>
        </div>
        @if (session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">{{ session('error') }}</div>
        @endif
    </div>
</div>



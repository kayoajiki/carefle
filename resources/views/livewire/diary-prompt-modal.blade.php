<div>
    @if($show)
    <div 
        x-data
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="$wire.dismiss()"
    >
        <div 
            class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6"
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <div class="text-center space-y-4">
                <div class="w-16 h-16 mx-auto bg-[#6BB6FF]/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                
                <h3 class="heading-3 text-xl text-[#2E5C8A]">今日の振り返りを書いてみませんか？</h3>
                
                <p class="body-text text-[#1E3A5F]/70">
                    日記を書くことで、自分の気持ちや考えを整理し、成長を実感できます。
                </p>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <button 
                        wire:click="writeDiary"
                        class="btn-primary flex-1"
                    >
                        今日の振り返りを書く
                    </button>
                    <button 
                        wire:click="dismiss"
                        class="btn-secondary flex-1"
                    >
                        後でやる
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

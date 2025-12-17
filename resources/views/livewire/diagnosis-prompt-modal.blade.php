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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                
                <h3 class="heading-3 text-xl text-[#2E5C8A]">現職満足度診断を完了しましょう</h3>
                
                <p class="body-text text-[#1E3A5F]/70">
                    診断を完了すると、あなたの満足度を可視化し、次のステップが見えてきます。
                </p>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <button 
                        wire:click="continueDiagnosis"
                        class="btn-primary flex-1"
                    >
                        診断を続ける
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

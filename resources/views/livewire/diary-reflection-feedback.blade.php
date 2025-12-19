<div class="card-refined p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/carekuma/carekuma-icon.png') }}" alt="キャリくま" class="w-6 h-6 object-cover">
            <h3 class="heading-3 text-lg text-[#2E5C8A]">AIからの内省フィードバック</h3>
        </div>
        @if(!$feedback && !$isLoading)
            <button
                wire:click="generateFeedback"
                wire:loading.attr="disabled"
                wire:target="generateFeedback"
                class="px-4 py-2 bg-[#6BB6FF] text-white body-small font-medium rounded-lg hover:bg-[#5AA5E6] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="generateFeedback">フィードバックを生成</span>
                <span wire:loading wire:target="generateFeedback" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    生成中...
                </span>
            </button>
        @endif
    </div>

    @if($error)
        <div class="bg-red-50 border border-red-200 text-red-800 body-small p-4 rounded-lg mb-4">
            {{ $error }}
        </div>
    @endif

    @if($isLoading)
        <div class="flex items-center justify-center py-8">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="w-3 h-3 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>
            <span class="body-small text-[#1E3A5F]/60 ml-3">フィードバックを生成中...</span>
        </div>
    @elseif($feedback)
        <div class="bg-[#E8F4FF] rounded-xl p-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white border border-[#6BB6FF] flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/carekuma/carekuma-icon.png') }}" alt="キャリくま" class="w-full h-full object-cover">
                </div>
                <div class="flex-1">
                    <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $feedback }}</p>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button
                    wire:click="generateFeedback"
                    wire:loading.attr="disabled"
                    wire:target="generateFeedback"
                    class="px-4 py-2 bg-white border border-[#6BB6FF] text-[#6BB6FF] body-small font-medium rounded-lg hover:bg-[#E8F4FF] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="generateFeedback">再生成</span>
                    <span wire:loading wire:target="generateFeedback" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-[#6BB6FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        生成中...
                    </span>
                </button>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <p class="body-text text-[#1E3A5F]/60 mb-4">AIがあなたの日記を読み、内省を深めるためのフィードバックを提供します。</p>
            <p class="body-small text-[#1E3A5F]/40">「フィードバックを生成」ボタンをクリックしてください。</p>
        </div>
    @endif
</div>

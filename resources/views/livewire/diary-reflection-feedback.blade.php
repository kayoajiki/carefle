<div class="card-refined p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="heading-3 text-lg text-[#2E5C8A]">AIからの内省フィードバック</h3>
        @if(!$feedback && !$isLoading)
            <button
                wire:click="generateFeedback"
                class="px-4 py-2 bg-[#6BB6FF] text-white body-small font-medium rounded-lg hover:bg-[#5AA5E6] transition-colors"
            >
                フィードバックを生成
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
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $feedback }}</p>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button
                    wire:click="generateFeedback"
                    class="px-4 py-2 bg-white border border-[#6BB6FF] text-[#6BB6FF] body-small font-medium rounded-lg hover:bg-[#E8F4FF] transition-colors"
                >
                    再生成
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


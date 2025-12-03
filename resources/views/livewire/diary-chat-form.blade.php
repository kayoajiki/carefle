<div class="flex flex-col h-full" x-data="{ 
    scrollToBottom() {
        this.$nextTick(() => {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    }
}" 
x-init="scrollToBottom()"
@scroll-to-bottom.window="scrollToBottom()">
    
    @if(session('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 body-small p-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 body-small p-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- ヘッダー --}}
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-[#2E5C8A]/20">
        <div>
            <h2 class="text-lg font-semibold text-[#2E5C8A]">内省チャット</h2>
            <p class="body-small text-[#1E3A5F]">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- モチベーションスライダー --}}
            <div class="flex items-center gap-2" x-data="{ show: @entangle('showMotivationSlider') }">
                <button
                    @click="show = !show"
                    class="px-3 py-1.5 rounded-lg bg-[#E8F4FF] text-[#2E5C8A] body-small hover:bg-[#D0E8FF] transition-colors"
                >
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        モチベーション: {{ $motivation }}
                    </span>
                </button>
                <div x-show="show" x-transition class="absolute mt-12 right-0 bg-white rounded-lg shadow-lg p-4 border border-[#2E5C8A]/20 z-10" style="display: none;">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">モチベーション（0〜100）</label>
                    <div class="flex items-center gap-3 w-64">
                        <input
                            type="range"
                            min="0"
                            max="100"
                            wire:model.live="motivation"
                            class="flex-1 accent-[#6BB6FF]"
                        />
                        <span class="body-text font-semibold text-[#2E5C8A] w-12 text-right">{{ $motivation }}</span>
                    </div>
                </div>
            </div>
            
            {{-- 保存ボタン --}}
            <button
                wire:click="saveConversation"
                class="px-4 py-2 rounded-lg bg-[#6BB6FF] text-white body-small font-medium hover:bg-[#5AA5E6] transition-colors"
            >
                保存
            </button>
        </div>
    </div>

    {{-- メッセージエリア --}}
    <div 
        x-ref="messagesContainer"
        class="flex-1 overflow-y-auto mb-4 space-y-4 pr-2"
        style="max-height: calc(100vh - 300px); min-height: 400px;"
    >
        @forelse($messages as $index => $message)
            @if($message['role'] === 'assistant')
                {{-- AIメッセージ（左側） --}}
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="bg-[#E8F4FF] rounded-2xl rounded-tl-sm px-4 py-3 max-w-[80%]">
                            <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>
                        @if(isset($message['timestamp']))
                            <p class="body-small text-[#1E3A5F]/60 mt-1 ml-1">
                                {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                {{-- ユーザーメッセージ（右側） --}}
                <div class="flex items-start gap-3 justify-end">
                    <div class="flex-1 flex justify-end">
                        <div class="bg-[#6BB6FF] rounded-2xl rounded-tr-sm px-4 py-3 max-w-[80%]">
                            <p class="body-text text-white whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#2E5C8A] flex items-center justify-center">
                        <span class="text-white text-xs font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>
                @if(isset($message['timestamp']))
                    <div class="flex justify-end">
                        <p class="body-small text-[#1E3A5F]/60 mt-1 mr-1">
                            {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                        </p>
                    </div>
                @endif
            @endif
        @empty
            <div class="flex flex-col items-center justify-center h-full gap-4">
                <p class="body-text text-[#1E3A5F]/60">会話を始めましょう</p>
                <button
                    wire:click="startNewConversation"
                    class="px-6 py-3 rounded-xl bg-[#6BB6FF] text-white body-text font-medium hover:bg-[#5AA5E6] transition-colors"
                >
                    内省を始める
                </button>
            </div>
        @endforelse

        {{-- ローディングインジケーター --}}
        @if($isLoading)
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <div class="bg-[#E8F4FF] rounded-2xl rounded-tl-sm px-4 py-3">
                    <div class="flex gap-1">
                        <div class="w-2 h-2 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                        <div class="w-2 h-2 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 bg-[#6BB6FF] rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- 入力エリア --}}
    <div class="border-t border-[#2E5C8A]/20 pt-4">
        <form wire:submit.prevent="sendMessage" class="flex items-end gap-3">
            <div class="flex-1">
                <textarea
                    wire:model="currentMessage"
                    rows="2"
                    placeholder="メッセージを入力..."
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#1E3A5F] px-4 py-3 body-text resize-none focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    x-on:keydown.enter.prevent="if(!$event.shiftKey) $wire.sendMessage()"
                ></textarea>
                <p class="body-small text-[#1E3A5F]/60 mt-1">Enterで送信、Shift+Enterで改行</p>
            </div>
            <button
                type="submit"
                :disabled="$wire.isLoading || !trim($wire.currentMessage)"
                class="px-6 py-3 rounded-xl bg-[#6BB6FF] text-white body-text font-medium hover:bg-[#5AA5E6] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                送信
            </button>
        </form>
    </div>
</div>

<div class="flex flex-col h-full" x-data="{ 
    isMobileDevice: false,
    init() {
        // タッチデバイスまたはモバイルユーザーエージェントを検出
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        const userAgent = navigator.userAgent.toLowerCase();
        const isMobileUA = /iphone|ipad|ipod|android/i.test(userAgent);
        this.isMobileDevice = isTouchDevice && (isMobileUA || window.innerWidth <= 768);
        
        // スクロールを最下部に
        this.scrollToBottom();
    },
    scrollToBottom() {
        this.$nextTick(() => {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    },
    isMessageEmpty() {
        try {
            if (!$wire || !$wire.currentMessage) return true;
            const msg = $wire.currentMessage;
            if (typeof msg === 'string') {
                return !msg.trim();
            }
            return !msg;
        } catch (e) {
            return true;
        }
    }
}" 
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
                class="px-4 py-2 rounded-lg {{ $conversationStep === 'closing' ? 'bg-[#4CAF50] hover:bg-[#45A049] ring-2 ring-[#4CAF50] ring-offset-2' : 'bg-[#6BB6FF] hover:bg-[#5AA5E6]' }} text-white body-small font-medium transition-colors"
            >
                @if($conversationStep === 'closing')
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        保存して完了
                    </span>
                @else
                    保存
                @endif
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
                <div class="flex items-start gap-3" wire:key="assistant-{{ $index }}-{{ md5($message['content'] ?? '') }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white border-2 border-[#6BB6FF] flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/carekuma/carekuma-icon.png') }}" alt="キャリくま" class="w-full h-full object-cover">
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
                        
                        {{-- 最後のクロージングメッセージの後にボタンを表示 --}}
                        @if($conversationStep === 'closing' && $index === count($messages) - 1 && $message['role'] === 'assistant')
                            <div class="mt-4 flex flex-col sm:flex-row gap-3">
                                <button
                                    wire:click="saveConversationAndClose"
                                    wire:loading.attr="disabled"
                                    wire:target="saveConversationAndClose"
                                    class="px-6 py-3 rounded-xl bg-[#4CAF50] text-white body-text font-medium hover:bg-[#45A049] transition-colors flex items-center justify-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="saveConversationAndClose" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>保存して閉じる<span class="text-xs opacity-90">（日記に保存されます）</span></span>
                                    </span>
                                    <span wire:loading wire:target="saveConversationAndClose" class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        保存中...
                                    </span>
                                </button>
                                <button
                                    wire:click="deleteConversationAndClose"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteConversationAndClose"
                                    wire:confirm="このチャットを削除して閉じますか？"
                                    class="px-6 py-3 rounded-xl bg-white border-2 border-red-400 text-red-600 body-text font-medium hover:bg-red-50 transition-colors flex items-center justify-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="deleteConversationAndClose">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        削除して閉じる
                                    </span>
                                    <span wire:loading wire:target="deleteConversationAndClose" class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        削除中...
                                    </span>
                                </button>
                            </div>
                        @endif
                        
                        {{-- 選択肢ボタン（最初のメッセージの後、かつ選択肢が表示される場合） --}}
                        @if($index === 0 && $showSelectionButtons && $message['role'] === 'assistant')
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button
                                    wire:click="selectTopic('work')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    仕事
                                </button>
                                <button
                                    wire:click="selectTopic('family')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    家族
                                </button>
                                <button
                                    wire:click="selectTopic('love')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    恋愛
                                </button>
                                <button
                                    wire:click="selectTopic('relationships')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    人間関係
                                </button>
                                <button
                                    wire:click="selectTopic('health')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    健康
                                </button>
                                <button
                                    wire:click="selectTopic('goals')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    目標
                                </button>
                                <button
                                    wire:click="selectTopic('learning')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    学び
                                </button>
                                <button
                                    wire:click="selectTopic('other')"
                                    class="px-4 py-2 rounded-lg bg-white border-2 border-[#6BB6FF] text-[#6BB6FF] body-text font-medium hover:bg-[#E8F4FF] transition-colors text-center"
                                >
                                    その他
                                </button>
                            </div>
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
    </div>

    {{-- 入力エリア --}}
    <div class="border-t border-[#2E5C8A]/20 pt-4">
        <form wire:submit.prevent="sendMessage" class="flex items-end gap-2 sm:gap-3">
            <div class="flex-1 min-w-0">
                <textarea
                    wire:model="currentMessage"
                    rows="2"
                    placeholder="メッセージを入力..."
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#1E3A5F] px-3 sm:px-4 py-2 sm:py-3 text-base resize-none focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    style="font-size: 16px;"
                    x-on:keydown.enter.prevent="!isMobileDevice && !$event.shiftKey ? $wire.sendMessage() : null"
                ></textarea>
                <p class="body-small text-[#1E3A5F]/60 mt-1" x-text="isMobileDevice ? '送信ボタンで送信' : 'Enterで送信、Shift+Enterで改行'"></p>
            </div>
            <button
                type="submit"
                x-bind:disabled="!$wire || $wire.isLoading || isMessageEmpty()"
                class="flex-shrink-0 p-2 sm:p-2.5 rounded-lg bg-[#6BB6FF] text-white hover:bg-[#5AA5E6] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>
</div>
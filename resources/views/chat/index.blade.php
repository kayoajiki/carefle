<x-layouts.app.sidebar :title="'チャット相談'">
    <flux:main>
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }
        </style>
        <div class="min-h-screen bg-[#F0F7FF] flex flex-col">
            <div class="flex-1 flex flex-col max-w-6xl mx-auto w-full content-padding section-spacing-sm">
                <!-- ヘッダー -->
                <div class="mb-12">
                    <h1 class="heading-2 mb-4">
                        チャット相談
                    </h1>
                    <p class="body-large">
                        気軽にキャリアについて相談できます
                    </p>
                </div>

                <!-- チャットエリア -->
                <div class="flex-1 flex flex-col card-refined overflow-hidden">
                    <!-- メッセージエリア -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chatMessages">
                        <!-- サンプルメッセージ（初期表示） -->
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#6BB6FF]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="bg-[#F0F7FF] rounded-2xl rounded-tl-sm p-4">
                                    <p class="text-sm text-[#1E3A5F] leading-relaxed">
                                        こんにちは！キャリフレのチャット相談です。<br>
                                        キャリアに関する疑問や不安を、気軽にお聞かせください。どのようなことでもお答えします。
                                    </p>
                                </div>
                                <p class="text-xs text-[#1E3A5F] mt-1.5 ml-1">
                                    カウンセラー • 今
                                </p>
                            </div>
                        </div>

                        <!-- メッセージが表示されるエリア -->
                    </div>

                    <!-- 入力エリア -->
                    <div class="border-t border-gray-200 p-4">
                        <form class="flex gap-3" id="chatForm">
                            <textarea rows="2" 
                                      id="messageInput"
                                      placeholder="メッセージを入力..."
                                      class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] text-[#1E3A5F] resize-none"
                                      required></textarea>
                            <button type="submit" 
                                    class="px-6 py-3 bg-[#6BB6FF] text-[#2E5C8A] font-semibold rounded-lg hover:bg-[#6BB6FF]/90 transition-colors flex-shrink-0 self-end">
                                送信
                            </button>
                        </form>
                        <p class="text-xs text-[#1E3A5F] mt-2 ml-1">
                            ※ この機能は現在開発中です。正式リリースをお待ちください。
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // チャット機能の基本実装（開発中）
            document.getElementById('chatForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                const message = input.value.trim();
                
                if (!message) return;

                // ユーザーメッセージを表示
                const messagesArea = document.getElementById('chatMessages');
                const userMessageDiv = document.createElement('div');
                userMessageDiv.className = 'flex items-start gap-3 justify-end';
                userMessageDiv.innerHTML = `
                    <div class="flex-1 max-w-[70%]">
                        <div class="bg-[#6BB6FF] text-[#2E5C8A] rounded-2xl rounded-tr-sm p-4 ml-auto">
                            <p class="text-sm leading-relaxed">${message}</p>
                        </div>
                        <p class="text-xs text-[#1E3A5F] mt-1.5 mr-1 text-right">あなた • 今</p>
                    </div>
                `;
                messagesArea.appendChild(userMessageDiv);
                input.value = '';
                messagesArea.scrollTop = messagesArea.scrollHeight;

                // 自動返信（開発中）
                setTimeout(() => {
                    const botMessageDiv = document.createElement('div');
                    botMessageDiv.className = 'flex items-start gap-3';
                    botMessageDiv.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-[#6BB6FF]/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="bg-[#F0F7FF] rounded-2xl rounded-tl-sm p-4">
                                <p class="text-sm text-[#1E3A5F] leading-relaxed">
                                    ご相談ありがとうございます。現在、チャット機能は開発中です。正式リリースまでしばらくお待ちください。緊急のご相談がございましたら、面談申し込みをご利用ください。
                                </p>
                            </div>
                            <p class="text-xs text-[#1E3A5F] mt-1.5 ml-1">カウンセラー • 今</p>
                        </div>
                    `;
                    messagesArea.appendChild(botMessageDiv);
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }, 1000);
            });
        </script>
    </flux:main>
</x-layouts.app.sidebar>


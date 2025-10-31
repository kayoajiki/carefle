<x-layouts.app.sidebar :title="'面談申し込み'">
    <flux:main>
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }
        </style>
        <div class="min-h-screen bg-[#f2f7f5] px-4 py-8 md:px-8">
            <div class="w-full max-w-4xl mx-auto">
                <!-- ヘッダー -->
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-[#00473e] mb-2">
                        面談申し込み
                    </h1>
                    <p class="text-base md:text-lg text-[#475d5b]">
                        キャリアカウンセラーとの1対1の面談を予約します
                    </p>
                </div>

                <!-- フォームカード -->
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-[#00473e] mb-2">
                            面談の流れ
                        </h2>
                        <div class="space-y-4 text-sm text-[#475d5b]">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-[#faae2b] text-[#00473e] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</div>
                                <div>
                                    <div class="font-semibold mb-1">お申し込み</div>
                                    <div>希望日時やご相談内容を入力して送信してください。</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-[#faae2b] text-[#00473e] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</div>
                                <div>
                                    <div class="font-semibold mb-1">日程調整</div>
                                    <div>カウンセラーから日程の確認メールをお送りします。</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-[#faae2b] text-[#00473e] font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</div>
                                <div>
                                    <div class="font-semibold mb-1">面談実施</div>
                                    <div>Zoomまたは対面で面談を行います（60分程度）。</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                お名前 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   value="{{ auth()->user()->name }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-[#475d5b]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                メールアドレス <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   value="{{ auth()->user()->email }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-[#475d5b]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                希望日時 <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" 
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#faae2b] focus:border-[#faae2b] text-[#475d5b]">
                            <p class="text-xs text-[#475d5b] mt-1.5">
                                複数の候補日時がある場合は、備考欄にご記載ください。
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                面談形式 <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="consultation_type" value="online" checked class="text-[#faae2b] focus:ring-[#faae2b]">
                                    <span class="text-sm text-[#475d5b]">オンライン（Zoom）</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="consultation_type" value="offline" class="text-[#faae2b] focus:ring-[#faae2b]">
                                    <span class="text-sm text-[#475d5b]">対面</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                ご相談内容 <span class="text-red-500">*</span>
                            </label>
                            <textarea rows="6" 
                                      required
                                      placeholder="現在の悩みや相談したいこと、面談で取り上げたいテーマなどをお書きください。"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#faae2b] focus:border-[#faae2b] text-[#475d5b] resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#00473e] mb-2">
                                備考
                            </label>
                            <textarea rows="4" 
                                      placeholder="その他、ご要望やご質問がございましたらご記入ください。"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#faae2b] focus:border-[#faae2b] text-[#475d5b] resize-none"></textarea>
                        </div>

                        <div class="pt-4 flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                申し込みを送信
                            </button>
                            <a href="{{ route('dashboard') }}" 
                               class="px-6 py-3 border-2 border-[#00473e] text-[#00473e] font-semibold rounded-lg hover:bg-[#00473e]/5 transition-colors">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>


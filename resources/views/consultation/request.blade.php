<x-layouts.app.sidebar :title="'面談申し込み'">
    <flux:main>
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }
        </style>
        <div class="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
            <div class="w-full max-w-4xl mx-auto">
                <!-- ヘッダー -->
                <div class="mb-12">
                    <h1 class="heading-2 mb-4">
                        面談申し込み
                    </h1>
                    <p class="body-large">
                        キャリアカウンセラーとの1対1の面談を予約します
                    </p>
                </div>

                <!-- フォームカード -->
                <div class="card-refined p-8 md:p-10">
                    <div class="mb-8">
                        <h2 class="heading-3 text-xl mb-6">
                            面談の流れ
                        </h2>
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-[#6BB6FF] text-[#2E5C8A] font-bold body-text flex items-center justify-center flex-shrink-0">1</div>
                                <div>
                                    <div class="body-text font-semibold mb-2">お申し込み</div>
                                    <div class="body-text">希望日時やご相談内容を入力して送信してください。</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-[#6BB6FF] text-[#2E5C8A] font-bold body-text flex items-center justify-center flex-shrink-0">2</div>
                                <div>
                                    <div class="body-text font-semibold mb-2">日程調整</div>
                                    <div class="body-text">カウンセラーから日程の確認メールをお送りします。</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-[#6BB6FF] text-[#2E5C8A] font-bold body-text flex items-center justify-center flex-shrink-0">3</div>
                                <div>
                                    <div class="body-text font-semibold mb-2">面談実施</div>
                                    <div class="body-text">Zoomまたは対面で面談を行います（60分程度）。</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                お名前 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   value="{{ auth()->user()->name }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-[#1E3A5F]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                メールアドレス <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   value="{{ auth()->user()->email }}" 
                                   readonly
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-[#1E3A5F]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                希望日時 <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" 
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] text-[#1E3A5F]">
                            <p class="text-xs text-[#1E3A5F] mt-1.5">
                                複数の候補日時がある場合は、備考欄にご記載ください。
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                面談形式 <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="consultation_type" value="online" checked class="text-[#6BB6FF] focus:ring-[#6BB6FF]">
                                    <span class="text-sm text-[#1E3A5F]">オンライン（Zoom）</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="consultation_type" value="offline" class="text-[#6BB6FF] focus:ring-[#6BB6FF]">
                                    <span class="text-sm text-[#1E3A5F]">対面</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                ご相談内容 <span class="text-red-500">*</span>
                            </label>
                            <textarea rows="6" 
                                      required
                                      placeholder="現在の悩みや相談したいこと、面談で取り上げたいテーマなどをお書きください。"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] text-[#1E3A5F] resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E5C8A] mb-2">
                                備考
                            </label>
                            <textarea rows="4" 
                                      placeholder="その他、ご要望やご質問がございましたらご記入ください。"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] text-[#1E3A5F] resize-none"></textarea>
                        </div>

                        <div class="pt-4 flex gap-4">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-[#6BB6FF] text-[#2E5C8A] font-semibold rounded-lg hover:bg-[#6BB6FF]/90 transition-colors">
                                申し込みを送信
                            </button>
                            <a href="{{ route('dashboard') }}" 
                               class="px-6 py-3 border-2 border-[#2E5C8A] text-[#2E5C8A] font-semibold rounded-lg hover:bg-[#2E5C8A]/5 transition-colors">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>


<div class="p-6 space-y-6">
    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
            {{ session('message') }}
        </div>
    @endif

    <div class="space-y-6">
        {{-- タイトル --}}
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">
                タイトル <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                wire:model="title"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                placeholder="例: 転職活動を開始する"
            />
            @error('title')
                <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- 目標日 --}}
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">
                目標日
            </label>
            <input
                type="date"
                wire:model="target_date"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            />
            @error('target_date')
                <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- テーマ --}}
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">
                テーマ
            </label>
            <input
                type="text"
                wire:model="mandalaCenter"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                placeholder="例: キャリアチェンジ"
            />
            @error('mandalaCenter')
                <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- 概要 --}}
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">
                概要
            </label>
            <textarea
                wire:model="summary"
                rows="4"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                placeholder="マイルストーンの詳細を記入してください"
            ></textarea>
            @error('summary')
                <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- 行動メモ自動生成ボタン --}}
        <div class="flex flex-col items-center gap-2">
            <button
                type="button"
                wire:click="generateActionItems"
                wire:loading.attr="disabled"
                wire:target="generateActionItems"
                @if(empty($title) || (empty($mandalaCenter) && empty($summary))) disabled @endif
                class="btn-primary flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg wire:loading wire:target="generateActionItems" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="generateActionItems">行動メモを自動生成</span>
                <span wire:loading wire:target="generateActionItems">生成中...</span>
            </button>
            <p class="text-xs text-slate-500 text-center">
                ※ AIで生成された行動メモは、ご確認の上で必要に応じて編集してください
            </p>
        </div>

        {{-- 行動メモ --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="body-small font-medium text-[#2E5C8A]">
                    行動メモ
                </label>
                <button
                    type="button"
                    wire:click="addActionItem"
                    class="btn-secondary text-xs">
                    ＋ 追加
                </button>
            </div>

            <div class="space-y-3">
                @foreach($actionItems as $index => $actionItem)
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="body-small text-slate-500">行動メモ {{ $index + 1 }}</span>
                            @if(count($actionItems) > 1)
                                <button
                                    type="button"
                                    wire:click="removeActionItem({{ $index }})"
                                    class="text-red-600 hover:text-red-700 body-small">
                                    削除
                                </button>
                            @endif
                        </div>

                        <div>
                            <label class="body-small text-slate-600 mb-1 block">タイトル</label>
                            <input
                                type="text"
                                wire:model="actionItems.{{ $index }}.title"
                                class="w-full rounded-lg border border-slate-200 bg-white text-[#2E5C8A] px-3 py-2 body-small focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF]"
                                placeholder="例: 履歴書を作成する"
                            />
                        </div>

                        <div>
                            <label class="body-small text-slate-600 mb-1 block">期限</label>
                            <input
                                type="date"
                                wire:model="actionItems.{{ $index }}.due_date"
                                class="w-full rounded-lg border border-slate-200 bg-white text-[#2E5C8A] px-3 py-2 body-small focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF]"
                            />
                        </div>

                        <div>
                            <label class="body-small text-slate-600 mb-1 block">メモ</label>
                            <textarea
                                wire:model="actionItems.{{ $index }}.notes"
                                rows="2"
                                class="w-full rounded-lg border border-slate-200 bg-white text-[#2E5C8A] px-3 py-2 body-small focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF]"
                                placeholder="詳細や注意点を記入"
                            ></textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 保存ボタン --}}
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
        <button
            type="button"
            wire:click="$dispatch('closeForm')"
            class="btn-secondary">
            キャンセル
        </button>
        <button
            type="button"
            wire:click="save"
            class="btn-primary">
            保存
        </button>
    </div>
</div>


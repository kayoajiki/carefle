<div class="card-refined p-8">
    {{-- 成功メッセージ --}}
    @if(session('message'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
            {{ session('message') }}
        </div>
    @endif

    {{-- エラーメッセージ --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 body-small p-4 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    {{-- アップロードフォーム --}}
    <div class="mb-8">
        <h2 class="heading-3 text-[#2E5C8A] mb-4">履歴書をアップロード</h2>
        
        <div class="border-2 border-dashed border-[#2E5C8A]/30 rounded-xl p-6 text-center hover:border-[#6BB6FF] transition-colors">
            <input
                type="file"
                wire:model="pdf"
                accept=".pdf,application/pdf"
                class="hidden"
                id="pdf-upload"
            />
            <label for="pdf-upload" class="cursor-pointer">
                <svg class="w-12 h-12 text-[#6BB6FF] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <p class="body-text text-[#2E5C8A] mb-1">PDFファイルを選択</p>
                <p class="body-small text-[#1E3A5F]">クリックまたはドラッグ&ドロップ（最大10MB）</p>
            </label>
        </div>

        @if($pdf)
            <div class="mt-4 p-4 bg-[#E8F4FF] rounded-xl border border-[#6BB6FF]">
                <p class="body-small text-[#1E3A5F] mb-2">選択されたファイル:</p>
                <p class="body-text text-[#2E5C8A] font-medium">{{ $pdf->getClientOriginalName() }}</p>
                <p class="body-small text-[#1E3A5F] mt-1">
                    サイズ: {{ number_format($pdf->getSize() / 1024, 2) }} KB
                </p>
            </div>
        @endif

        @error('pdf')
            <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
        @enderror

        @if($pdf)
            <div class="mt-4 flex justify-end">
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="btn-primary"
                >
                    <span wire:loading.remove>アップロード</span>
                    <span wire:loading>アップロード中...</span>
                </button>
            </div>
        @endif
    </div>

    {{-- アップロード済みドキュメント一覧 --}}
    @if($documents && $documents->count() > 0)
        <div>
            <h2 class="heading-3 text-[#2E5C8A] mb-4">アップロード済みの履歴書</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-[#2E5C8A]/20">
                            <th class="text-left py-3 px-4 body-small font-medium text-[#2E5C8A]">ファイル名</th>
                            <th class="text-left py-3 px-4 body-small font-medium text-[#2E5C8A]">アップロード日時</th>
                            <th class="text-left py-3 px-4 body-small font-medium text-[#2E5C8A]">ファイルサイズ</th>
                            <th class="text-right py-3 px-4 body-small font-medium text-[#2E5C8A]">操作</th>
                            <th class="text-left py-3 px-4 body-small font-medium text-[#2E5C8A]">メモ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr class="border-b border-[#2E5C8A]/10 hover:bg-[#E8F4FF]/30">
                                <td class="py-3 px-4 body-text text-[#1E3A5F]">
                                    <a 
                                        href="{{ route('resume.view', $document->id) }}" 
                                        target="_blank"
                                        class="text-[#2E5C8A] hover:text-[#6BB6FF] hover:underline transition-colors"
                                    >
                                        {{ $document->original_filename }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 body-small text-[#1E3A5F]">
                                    {{ $document->uploaded_at ? $document->uploaded_at->format('Y年m月d日 H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 body-small text-[#1E3A5F]">
                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a 
                                            href="{{ route('resume.view', $document->id) }}" 
                                            target="_blank"
                                            class="body-small text-[#2E5C8A] hover:text-[#6BB6FF] transition-colors"
                                        >
                                            閲覧
                                        </a>
                                        <button
                                            wire:click="delete({{ $document->id }})"
                                            wire:confirm="この履歴書を削除しますか？"
                                            class="body-small text-red-600 hover:text-red-800 transition-colors"
                                        >
                                            削除
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <input
                                        type="text"
                                        value="{{ $document->memo ?? '' }}"
                                        maxlength="20"
                                        placeholder="メモ（20文字まで）"
                                        class="w-full px-3 py-2 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#1E3A5F] body-small focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                                        wire:blur="updateMemo({{ $document->id }}, $event.target.value)"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <p class="body-text text-[#1E3A5F]">アップロード済みの履歴書はありません。</p>
        </div>
    @endif
</div>

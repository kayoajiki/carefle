<div class="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-12 gap-4">
            <h1 class="heading-2">WCMシート（Step 2/2）</h1>
            <div class="flex items-center gap-3 flex-wrap">
                <button wire:click="save" class="btn-primary text-sm">上書き保存</button>
                <button wire:click="saveAsNew" class="btn-secondary text-sm">新規保存</button>
                <button
                    onclick="if(!confirm('このシートを削除します。よろしいですか？')) return false;"
                    wire:click="delete({{ $sheet->id }})"
                    class="text-sm px-4 py-2 rounded-xl border-2 border-red-400 text-red-600 bg-white font-medium hover:bg-red-50 transition-colors"
                >削除</button>
            </div>
        </div>

        @if (session('saved'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm p-3 rounded-md">{{ session('saved') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">{{ session('error') }}</div>
        @endif

        {{-- 上部：固定ベン図（1920x1080 PNG を想定／レスポンシブ最適化） --}}
        <div class="card-refined mb-12 overflow-hidden pt-4 pb-0 px-0">
            @php($venn = public_path('images/wcm-venn.png'))
            @if(file_exists($venn))
                <img
                    src="{{ asset('images/wcm-venn.png') }}"
                    alt="WCM ベン図"
                    width="1920"
                    height="1080"
                    loading="lazy"
                    decoding="async"
                    class="w-full h-auto object-cover select-none block border-0 outline-none"
                    style="border: none; outline: none; box-shadow: none; display: block;"
                    sizes="(min-width: 1280px) 1120px, 100vw"
                />
            @else
                <div class="w-full aspect-[16/9] flex items-center justify-center body-text bg-[#F0F7FF] p-8">
                    画像（images/wcm-venn.png）を配置するとここに表示されます（推奨: 1920×1080 / PNG）
                </div>
            @endif
        </div>

        {{-- 下部：Will / Can / Must --}}
        <div class="space-y-8 mb-12">
            <div class="card-refined p-8 border-2 border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="heading-3 text-xl">Will</div>
                    <button
                        wire:click="generateWill"
                        wire:loading.attr="disabled"
                        wire:target="generateWill"
                        class="btn-secondary text-sm"
                    >
                        <span wire:loading.remove wire:target="generateWill">AI自動生成入力</span>
                        <span wire:loading wire:target="generateWill" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            生成中...
                        </span>
                    </button>
                </div>
                <textarea wire:model.debounce.800ms="will_text" rows="10" class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"></textarea>
            </div>
            <div class="card-refined p-8 border-2 border-amber-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="heading-3 text-xl">Can</div>
                    <button
                        wire:click="generateCan"
                        wire:loading.attr="disabled"
                        wire:target="generateCan"
                        class="btn-secondary text-sm"
                    >
                        <span wire:loading.remove wire:target="generateCan">AI自動生成入力</span>
                        <span wire:loading wire:target="generateCan" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            生成中...
                        </span>
                    </button>
                </div>
                <textarea wire:model.debounce.800ms="can_text" rows="10" class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"></textarea>
            </div>
            <div class="card-refined p-8 border-2 border-green-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="heading-3 text-xl">Must</div>
                    <button
                        wire:click="generateMust"
                        wire:loading.attr="disabled"
                        wire:target="generateMust"
                        class="btn-secondary text-sm"
                    >
                        <span wire:loading.remove wire:target="generateMust">AI自動生成入力</span>
                        <span wire:loading wire:target="generateMust" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            生成中...
                        </span>
                    </button>
                </div>
                <textarea wire:model.debounce.800ms="must_text" rows="10" class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"></textarea>
            </div>
        </div>

        {{-- バージョン一覧 --}}
        <div class="card-refined p-8">
            <div class="heading-3 text-xl mb-6">保存済み（最新10件）</div>
            <div class="space-y-3">
                @foreach($versions as $v)
                    <div class="flex items-center gap-3">
                        <a href="{{ route('wcm.sheet', ['id' => $v->id]) }}" class="px-4 py-2 rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] body-text hover:bg-[#E8F4FF] transition-colors">
                            v{{ $v->version }} （{{ $v->created_at->format('Y/m/d') }}）
                        </a>
                        <button
                            onclick="if(!confirm('v{{ $v->version }} を削除します。よろしいですか？')) return false;"
                            wire:click="delete({{ $v->id }})"
                            class="text-xs px-3 py-1.5 rounded-lg border-2 border-red-400 text-red-600 bg-white hover:bg-red-50 transition-colors"
                        >削除</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen bg-[#f2f7f5] px-4 py-10">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-start justify-between mb-6">
            <h1 class="text-3xl font-semibold text-[#00473e]">WCMシート（Step 2/2）</h1>
            <div class="flex items-center gap-3">
                <button wire:click="save" class="text-xs px-4 py-2 rounded-md font-semibold bg-[#faae2b] text-[#00473e] shadow-sm">上書き保存</button>
                <button wire:click="saveAsNew" class="text-xs px-4 py-2 rounded-md border border-[#00473e]/30 text-[#00473e] bg-white font-medium">新規保存</button>
                <button
                    onclick="if(!confirm('このシートを削除します。よろしいですか？')) return false;"
                    wire:click="delete({{ $sheet->id }})"
                    class="text-xs px-4 py-2 rounded-md border border-[#fa5246] text-[#fa5246] bg-white font-medium"
                >削除</button>
            </div>
        </div>

        @if (session('saved'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm p-3 rounded-md">{{ session('saved') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">{{ session('error') }}</div>
        @endif

        {{-- 上部：固定ベン図（ダミー画像 or SVG） --}}
        <div class="bg-white rounded-2xl border border-[#00332c]/10 shadow-sm p-6 mb-8">
            <div class="text-sm text-[#475d5b]">ベン図エリア（固定）</div>
            <div class="mt-2 w-full h-48 bg-[#f2f7f5] rounded"></div>
        </div>

        {{-- 下部：Will / Can / Must --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-[#a5b4fc] shadow-sm p-6">
                <div class="text-sm font-semibold text-[#00473e] mb-2">Will</div>
                <textarea wire:model.debounce.800ms="will_text" rows="10" class="w-full text-base rounded-md border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e] p-4 focus:outline-none focus:ring-2 focus:ring-[#faae2b]"></textarea>
            </div>
            <div class="bg-white rounded-xl border border-[#fdba74] shadow-sm p-6">
                <div class="text-sm font-semibold text-[#00473e] mb-2">Can</div>
                <textarea wire:model.debounce.800ms="can_text" rows="10" class="w-full text-base rounded-md border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e] p-4 focus:outline-none focus:ring-2 focus:ring-[#faae2b]"></textarea>
            </div>
            <div class="bg-white rounded-xl border border-[#86efac] shadow-sm p-6">
                <div class="text-sm font-semibold text-[#00473e] mb-2">Must</div>
                <textarea wire:model.debounce.800ms="must_text" rows="10" class="w-full text-base rounded-md border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e] p-4 focus:outline-none focus:ring-2 focus:ring-[#faae2b]"></textarea>
            </div>
        </div>

        {{-- バージョン一覧 --}}
        <div class="mt-8 bg-white rounded-xl border border-[#00332c]/10 shadow-sm p-4">
            <div class="text-sm font-semibold text-[#00473e] mb-2">保存済み（最新10件）</div>
            <div class="space-y-2">
                @foreach($versions as $v)
                    <div class="flex items-center gap-2 text-sm">
                        <a href="{{ route('wcm.sheet', ['id' => $v->id]) }}" class="px-3 py-1 rounded border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e]">
                            v{{ $v->version }} （{{ $v->created_at->format('Y/m/d') }}）
                        </a>
                        <button
                            onclick="if(!confirm('v{{ $v->version }} を削除します。よろしいですか？')) return false;"
                            wire:click="delete({{ $v->id }})"
                            class="text-xs px-2 py-1 rounded border border-[#fa5246] text-[#fa5246] bg-white"
                        >削除</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>



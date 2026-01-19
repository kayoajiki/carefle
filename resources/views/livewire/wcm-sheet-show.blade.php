<div class="min-h-screen bg-[#F0F7FF] content-padding section-spacing-sm">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-12 gap-4">
            <h1 class="heading-2">WCMシート（Step 2/2）</h1>
            <div class="flex items-center gap-3 flex-wrap">
                @if(!$isAdminView)
                    <button wire:click="save" class="btn-primary text-sm">上書き保存</button>
                    <button wire:click="saveAsNew" class="btn-secondary text-sm">新規保存</button>
                    @if($sheet->is_admin_visible)
                        <span class="text-sm px-3 py-2 rounded-xl bg-green-50 border border-green-300 text-green-700 font-medium">
                            管理者に共有中
                        </span>
                        <form action="{{ route('share-preview.unshare') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="type" value="wcm">
                            <input type="hidden" name="id" value="{{ $sheet->id }}">
                            <button type="submit" onclick="return confirm('共有を解除しますか？')" class="btn-secondary text-sm">
                                共有を解除
                            </button>
                        </form>
                    @else
                        <a href="{{ route('share-preview.wcm', ['id' => $sheet->id]) }}" class="btn-secondary text-sm">
                            管理者に共有する
                        </a>
                    @endif
                    <button
                        onclick="if(!confirm('このシートを削除します。よろしいですか？')) return false;"
                        wire:click="delete({{ $sheet->id }})"
                        class="text-sm px-4 py-2 rounded-xl border-2 border-red-400 text-red-600 bg-white font-medium hover:bg-red-50 transition-colors"
                    >削除</button>
                @else
                    <span class="text-sm px-3 py-2 rounded-xl bg-blue-50 border border-blue-300 text-blue-700 font-medium">
                        管理者閲覧モード（閲覧のみ）
                    </span>
                    <a href="{{ route('admin.users.show', ['user' => $sheet->user_id]) }}" class="btn-secondary text-sm">
                        ユーザー詳細に戻る
                    </a>
                @endif
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
                        @if($isAdminView) disabled @endif
                        class="btn-secondary text-sm @if($isAdminView) opacity-50 cursor-not-allowed @endif"
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
                <textarea wire:model.debounce.800ms="will_text" rows="10" @if($isAdminView) readonly @endif class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all @if($isAdminView) opacity-75 @endif"></textarea>
            </div>
            <div class="card-refined p-8 border-2 border-amber-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="heading-3 text-xl">Can</div>
                    <button
                        wire:click="generateCan"
                        wire:loading.attr="disabled"
                        wire:target="generateCan"
                        @if($isAdminView) disabled @endif
                        class="btn-secondary text-sm @if($isAdminView) opacity-50 cursor-not-allowed @endif"
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
                <textarea wire:model.debounce.800ms="can_text" rows="10" @if($isAdminView) readonly @endif class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all @if($isAdminView) opacity-75 @endif"></textarea>
            </div>
            <div class="card-refined p-8 border-2 border-green-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="heading-3 text-xl">Must</div>
                    <button
                        wire:click="generateMust"
                        wire:loading.attr="disabled"
                        wire:target="generateMust"
                        @if($isAdminView) disabled @endif
                        class="btn-secondary text-sm @if($isAdminView) opacity-50 cursor-not-allowed @endif"
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
                <textarea wire:model.debounce.800ms="must_text" rows="10" @if($isAdminView) readonly @endif class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all @if($isAdminView) opacity-75 @endif"></textarea>
            </div>
        </div>

        {{-- バージョン一覧 --}}
        @if(!$isAdminView)
        <div class="card-refined p-8">
            <div class="heading-3 text-xl mb-6">保存済み（最新10件）</div>
            <div class="space-y-3">
                @foreach($versions as $v)
                    <div class="flex items-center gap-3">
                        <a href="{{ route('wcm.sheet', ['id' => $v->id]) }}" class="px-4 py-2 rounded-xl border-2 {{ $v->id === $sheet->id ? 'border-[#6BB6FF] bg-[#E8F4FF]' : 'border-[#2E5C8A]/20 bg-[#F0F7FF]' }} text-[#2E5C8A] body-text hover:bg-[#E8F4FF] transition-colors">
                            v{{ $v->version }} （{{ $v->created_at->format('Y/m/d') }}）
                        </a>
                        @if($v->is_admin_visible)
                            <span class="text-xs px-2 py-1 rounded bg-green-50 border border-green-300 text-green-700 font-medium">
                                共有中
                            </span>
                        @endif
                        <button
                            onclick="if(!confirm('v{{ $v->version }} を削除します。よろしいですか？')) return false;"
                            wire:click="delete({{ $v->id }})"
                            class="text-xs px-3 py-1.5 rounded-lg border-2 border-red-400 text-red-600 bg-white hover:bg-red-50 transition-colors"
                        >削除</button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- AI生成提案モーダル --}}
    @if($showProposalModal)
    <div 
        x-data
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="$wire.closeProposalModal()"
    >
        <div 
            class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
            wire:click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <div class="p-6 md:p-8">
                {{-- ヘッダー --}}
                <div class="flex items-center justify-between mb-6">
                    <h2 class="heading-2">
                        @if($proposalType === 'will')
                            WillのAI生成提案
                        @elseif($proposalType === 'can')
                            CanのAI生成提案
                        @elseif($proposalType === 'must')
                            MustのAI生成提案
                        @endif
                    </h2>
                    <button 
                        wire:click="closeProposalModal"
                        class="text-[#1E3A5F]/50 hover:text-[#1E3A5F] transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if(empty($editingProposal))
                    {{-- 提案表示モード --}}
                    <div class="space-y-6">
                        {{-- 既存の内容 --}}
                        <div>
                            <h3 class="heading-3 text-lg mb-3 text-[#2E5C8A]">既存の内容</h3>
                            <div class="bg-[#F0F7FF] rounded-xl p-4 border border-[#2E5C8A]/20">
                                <p class="body-text text-[#1E3A5F] whitespace-pre-wrap min-h-[100px]">
                                    @if($proposalType === 'will')
                                        {{ $will_text ?: '（未入力）' }}
                                    @elseif($proposalType === 'can')
                                        {{ $can_text ?: '（未入力）' }}
                                    @elseif($proposalType === 'must')
                                        {{ $must_text ?: '（未入力）' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- AI生成された提案 --}}
                        <div>
                            <h3 class="heading-3 text-lg mb-3 text-[#2E5C8A]">AI生成された提案</h3>
                            <div class="bg-blue-50 rounded-xl p-4 border-2 border-blue-200">
                                <p class="body-text text-[#1E3A5F] whitespace-pre-wrap min-h-[100px]">
                                    @if($proposalType === 'will')
                                        {{ $proposedWill }}
                                    @elseif($proposalType === 'can')
                                        {{ $proposedCan }}
                                    @elseif($proposalType === 'must')
                                        {{ $proposedMust }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- アクションボタン --}}
                        <div class="border-t border-[#2E5C8A]/20 pt-6">
                            <div class="flex flex-col gap-3">
                                <p class="body-small text-[#1E3A5F]/70 mb-2">提案の適用方法を選択してください：</p>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        wire:click="acceptProposal('replace')"
                                        class="btn-primary flex-1 min-w-[120px]"
                                    >
                                        置き換える
                                    </button>
                                    <button
                                        wire:click="acceptProposal('append')"
                                        class="btn-secondary flex-1 min-w-[120px]"
                                    >
                                        追加する
                                    </button>
                                    <button
                                        wire:click="acceptProposal('merge')"
                                        class="btn-secondary flex-1 min-w-[120px]"
                                    >
                                        マージする
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-3 mt-2">
                                    <button
                                        wire:click="editProposal"
                                        class="btn-secondary flex-1 min-w-[120px]"
                                    >
                                        編集する
                                    </button>
                                    <button
                                        wire:click="rejectProposal"
                                        class="btn-secondary flex-1 min-w-[120px] border-red-300 text-red-600 hover:bg-red-50"
                                    >
                                        破棄する
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- 編集モード --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="heading-3 text-lg mb-3 text-[#2E5C8A]">提案を編集</h3>
                            <textarea
                                wire:model="editingProposal"
                                rows="12"
                                class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                                placeholder="提案を編集してください"
                            ></textarea>
                        </div>

                        <div class="border-t border-[#2E5C8A]/20 pt-6">
                            <div class="flex flex-col gap-3">
                                <p class="body-small text-[#1E3A5F]/70 mb-2">編集した提案の適用方法を選択してください：</p>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        wire:click="applyEditedProposal('replace')"
                                        class="btn-primary flex-1 min-w-[120px]"
                                    >
                                        置き換える
                                    </button>
                                    <button
                                        wire:click="applyEditedProposal('append')"
                                        class="btn-secondary flex-1 min-w-[120px]"
                                    >
                                        追加する
                                    </button>
                                    <button
                                        wire:click="applyEditedProposal('merge')"
                                        class="btn-secondary flex-1 min-w-[120px]"
                                    >
                                        マージする
                                    </button>
                                </div>
                                <button
                                    wire:click="closeProposalModal"
                                    class="btn-secondary mt-2"
                                >
                                    キャンセル
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

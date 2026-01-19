<x-admin.layouts.app title="マイルストーン: {{ $user->name }}">
    <div class="min-h-screen w-full bg-[#F0F7FF] text-[#1E3A5F] content-padding section-spacing-sm">
        {{-- ヘッダー --}}
        <div class="max-w-4xl mx-auto mb-12">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
                <div>
                    <h1 class="heading-2 mb-4">
                        {{ $user->name }}さんのマイルストーン
                    </h1>
                    <p class="body-large">
                        ユーザーが設定したマイルストーンを確認できます。
                    </p>
                </div>
                <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" class="btn-secondary text-sm">
                    ユーザー詳細に戻る
                </a>
            </div>
        </div>

        {{-- マイルストーン表示 --}}
        <div class="max-w-4xl mx-auto">
            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-6">{{ $milestone->title }}</h2>
                
                <div class="space-y-6">
                    {{-- 基本情報 --}}
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="body-small text-[#1E3A5F]/70 mb-1">目標年</p>
                                <p class="body-text text-[#2E5C8A] font-semibold">{{ $milestone->target_year }}年</p>
                            </div>
                            @if($milestone->target_date)
                            <div>
                                <p class="body-small text-[#1E3A5F]/70 mb-1">目標日</p>
                                <p class="body-text text-[#2E5C8A] font-semibold">{{ $milestone->target_date->format('Y年n月j日') }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="body-small text-[#1E3A5F]/70 mb-1">ステータス</p>
                                <span class="px-3 py-1 rounded-lg bg-[#F0F7FF] border border-[#2E5C8A]/10 text-sm">
                                    {{ $milestone->status === 'achieved' ? '達成' : ($milestone->status === 'in_progress' ? '進行中' : '計画中') }}
                                </span>
                            </div>
                            @if($milestone->will_theme)
                            <div>
                                <p class="body-small text-[#1E3A5F]/70 mb-1">Willテーマ</p>
                                <p class="body-text text-[#2E5C8A] font-semibold">{{ $milestone->will_theme }}</p>
                            </div>
                            @endif
                        </div>

                        @if($milestone->description)
                        <div class="mt-4">
                            <p class="body-small text-[#1E3A5F]/70 mb-2">説明</p>
                            <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $milestone->description }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- アクションアイテム --}}
                    @if($milestone->actionItems->count() > 0)
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">アクションアイテム ({{ $milestone->actionItems->count() }}件)</h3>
                        <div class="space-y-3">
                            @foreach($milestone->actionItems as $action)
                            <div class="border-b border-[#2E5C8A]/10 pb-3 last:border-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="body-text text-[#2E5C8A] font-semibold mb-1">{{ $action->title }}</p>
                                        @if($action->due_date)
                                        <p class="body-small text-[#1E3A5F]/70">期限: {{ $action->due_date->format('Y年n月j日') }}</p>
                                        @endif
                                        @if($action->notes)
                                        <p class="body-small text-[#1E3A5F] mt-2">{{ $action->notes }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs ml-4 {{ $action->status === 'completed' ? 'bg-green-50 border border-green-300 text-green-700' : 'bg-yellow-50 border border-yellow-300 text-yellow-700' }}">
                                        {{ $action->status === 'completed' ? '完了' : '未完了' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>

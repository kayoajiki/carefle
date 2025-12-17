<div class="content-padding section-spacing-sm">
    <div class="max-w-6xl mx-auto space-y-10">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="body-small text-slate-500">未来の地図を、直感的に俯瞰・更新できます。</p>
                <h1 class="heading-2">マイルストーンボード</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="btn-secondary text-sm" wire:click="openCreateForm">
                    ＋ 新規マイルストーン
                </button>
                @if($detailMilestone)
                    <button type="button" class="btn-primary text-sm" wire:click="openEditForm({{ $detailMilestone->id }})">
                        編集する
                    </button>
                @endif
            </div>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="card-refined soft-shadow-refined p-6">
                <p class="body-small text-slate-500">次の締切</p>
                <p class="heading-3 text-xl mt-1">
                    @if($summary['next'])
                        {{ $summary['next']->target_date?->format('Y/m/d') }}
                    @else
                        日付未設定
                    @endif
                </p>
                <p class="body-small text-slate-600 mt-1">
                    {{ $summary['next']->title ?? 'まずは1件登録してみましょう' }}
                </p>
            </div>
            <div class="card-refined soft-shadow-refined p-6">
                <p class="body-small text-slate-500">未完了アクション</p>
                <p class="heading-3 text-3xl mt-1">{{ $summary['pendingActions'] }}</p>
                <p class="body-small text-slate-600 mt-1">今日の一歩を決めましょう</p>
            </div>
            <div class="card-refined soft-shadow-refined p-6">
                <p class="body-small text-slate-500">今月〜30日以内</p>
                <p class="heading-3 text-3xl mt-1">{{ $summary['within30Days'] }}</p>
                <p class="body-small text-slate-600 mt-1">集中して進めるテーマ数</p>
            </div>
        </section>

        <section class="grid gap-8 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="heading-3 text-xl">マイルストーン一覧</h2>
                    <span class="body-small text-slate-500">{{ $summary['total'] }} 件</span>
                </div>
                <div class="space-y-5">
                    @forelse($groups as $label => $items)
                        <div class="space-y-3">
                            <p class="body-small uppercase tracking-wide text-slate-500">{{ $label }}</p>
                            @forelse($items as $item)
                                <button type="button"
                                    class="w-full text-left card-refined soft-shadow-refined p-5 border {{ $selectedMilestoneId === $item->id ? 'border-[#6BB6FF]' : 'border-transparent' }}"
                                    wire:click="selectMilestone({{ $item->id }})">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="body-text font-semibold text-[#2E5C8A]">{{ $item->title }}</p>
                                            <p class="body-small text-slate-500 mt-1">
                                                {{ $item->target_date?->format('Y/m/d') ?? '日付未設定' }}
                                            </p>
                                        </div>
                                        <span class="body-small text-slate-500">
                                            {{ $item->actionItems->where('status', 'pending')->count() }} 件の行動
                                        </span>
                                    </div>
                                    @if($item->description)
                                        <p class="body-small text-slate-500 mt-3">
                                            {{ \Illuminate\Support\Str::limit($item->description, 70) }}
                                        </p>
                                    @endif
                                </button>
                            @empty
                                <p class="body-small text-slate-400">該当なし</p>
                            @endforelse
                        </div>
                    @empty
                        <p class="body-small text-slate-500">まだマイルストーンは登録されていません。</p>
                    @endforelse
                </div>
            </div>

            <div class="card-refined soft-shadow-refined p-6 space-y-6">
                @if($detailMilestone)
                    <div class="space-y-2">
                        <p class="body-small text-slate-500">フォーカス中</p>
                        <h2 class="heading-3 text-2xl">{{ $detailMilestone->title }}</h2>
                        <div class="flex flex-wrap gap-2 body-small text-slate-600">
                            <span class="px-3 py-1 rounded-2xl bg-slate-100">
                                {{ $detailMilestone->target_date?->format('Y/m/d') ?? '日付未設定' }}
                            </span>
                            @if($detailMilestone->mandala_data['center'] ?? null)
                                <span class="px-3 py-1 rounded-2xl bg-[#E6F0FF] text-[#2E5C8A]">
                                    {{ $detailMilestone->mandala_data['center'] }}
                                </span>
                            @endif
                        </div>
                        @if($detailMilestone->description)
                            <p class="body-text text-slate-600 mt-3">{{ $detailMilestone->description }}</p>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <p class="body-small text-slate-500">行動メモ</p>
                        <div class="space-y-3">
                            @forelse($detailMilestone->actionItems as $action)
                                <div class="border border-slate-200 rounded-2xl p-4 flex flex-col gap-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="body-text font-semibold text-[#2E5C8A]">{{ $action->title }}</p>
                                        <span class="body-small text-slate-500">
                                            {{ $action->due_date?->format('m/d') ?? 'いつでも' }}
                                        </span>
                                    </div>
                                    @if($action->description)
                                        <p class="body-small text-slate-500">{{ $action->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($action->status === 'completed')
                                            <span class="body-small text-green-600">完了済み</span>
                                        @else
                                            <button type="button"
                                                class="btn-secondary text-xs"
                                                wire:click="markActionDone({{ $action->id }})">
                                                ✓ 完了
                                            </button>
                                        @endif
                                        
                                        {{-- 移動先選択 --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button type="button"
                                                @click="open = !open"
                                                class="btn-secondary text-xs">
                                                移動
                                            </button>
                                            <div x-show="open"
                                                @click.away="open = false"
                                                x-transition
                                                class="absolute top-full left-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-10 min-w-[200px] max-h-60 overflow-y-auto"
                                                style="display: none;">
                                                <div class="p-2 space-y-1">
                                                    @foreach($milestones as $milestone)
                                                        @if($milestone->id !== $detailMilestone->id)
                                                            <button type="button"
                                                                wire:click="moveActionItem({{ $action->id }}, {{ $milestone->id }})"
                                                                @click="open = false"
                                                                class="w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 body-small text-slate-600">
                                                                {{ $milestone->title }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                    @if($milestones->count() <= 1)
                                                        <p class="px-3 py-2 body-small text-slate-400">他のマイルストーンがありません</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- 削除ボタン --}}
                                        <button type="button"
                                            wire:click="deleteActionItem({{ $action->id }})"
                                            wire:confirm="この行動メモを削除しますか？"
                                            class="btn-secondary text-xs text-red-600 hover:text-red-700">
                                            削除
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="body-small text-slate-500">まだ行動メモがありません。</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="body-small text-slate-500">ミニマンダラ</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(($detailMilestone->mandala_data['elements'] ?? []) as $index => $idea)
                                <div class="border border-slate-200 rounded-2xl p-3">
                                    <p class="body-small font-semibold text-[#2E5C8A]">キーワード {{ $index + 1 }}</p>
                                    <p class="body-small text-slate-600 mt-1">{{ $idea ?: '---' }}</p>
                                    <p class="body-small text-slate-500 mt-2">
                                        {{ $detailMilestone->mandala_data['actions'][$index] ?? 'アクション未記入' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center space-y-3">
                        <p class="heading-3 text-xl text-[#2E5C8A]">まだマイルストーンがありません</p>
                        <p class="body-small text-slate-500">まずは「新規マイルストーン」ボタンから登録してみましょう。</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="card-refined soft-shadow-refined p-6 space-y-4">
            <h2 class="heading-3 text-xl">期間別タイムライン</h2>
            <div class="space-y-6">
                @foreach($groups as $label => $items)
                    <div>
                        <p class="body-small text-slate-500 mb-2">{{ $label }}</p>
                        <div class="flex flex-col gap-2">
                            @forelse($items as $item)
                                <div class="flex items-center gap-4">
                                    <div class="w-20 body-small text-[#2E5C8A]">
                                        {{ $item->target_date?->format('m/d') ?? '--/--' }}
                                    </div>
                                    <div class="flex-1 border border-slate-200 rounded-2xl px-4 py-2 body-small text-slate-600">
                                        {{ $item->title }}
                                    </div>
                                </div>
                            @empty
                                <p class="body-small text-slate-400">予定なし</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    @if($showForm)
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-30 p-4">
            <div class="bg-white rounded-3xl w-full max-w-3xl max-h-[95vh] overflow-y-auto relative">
                <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600" wire:click="closeForm">✕</button>
                @livewire('career-milestone-form', ['milestoneId' => $formMilestoneId], key('milestone-form-'.$formMilestoneId))
            </div>
        </div>
    @endif
</div>



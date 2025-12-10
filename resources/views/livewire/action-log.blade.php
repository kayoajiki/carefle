<div class="card-refined p-8">
    <h2 class="heading-2 text-2xl text-[#2E5C8A] mb-6">アクションログ</h2>

    {{-- フィルター --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">期間</label>
            <select
                wire:model.live="filterPeriod"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            >
                <option value="week">過去1週間</option>
                <option value="month">過去1ヶ月</option>
                <option value="quarter">過去3ヶ月</option>
                <option value="year">過去1年</option>
                <option value="all">すべて</option>
            </select>
        </div>
        <div>
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">タイプ</label>
            <select
                wire:model.live="filterType"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            >
                <option value="all">すべて</option>
                <option value="action_items">アクションアイテム</option>
                <option value="from_diary">日記から抽出</option>
            </select>
        </div>
    </div>

    {{-- 統計 --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-[#E8F4FF] rounded-xl p-4">
            <p class="body-small text-[#1E3A5F]/60 mb-1">総アクション数</p>
            <p class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $total_count }}</p>
        </div>
        <div class="bg-[#E8F4FF] rounded-xl p-4">
            <p class="body-small text-[#1E3A5F]/60 mb-1">完了アクション</p>
            <p class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $completed_actions_count }}</p>
        </div>
        <div class="bg-[#E8F4FF] rounded-xl p-4">
            <p class="body-small text-[#1E3A5F]/60 mb-1">日記から抽出</p>
            <p class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $diary_actions_count }}</p>
        </div>
    </div>

    {{-- アクションリスト --}}
    @if(empty($actions))
        <div class="text-center py-12">
            <p class="body-text text-[#1E3A5F]/60">アクションが見つかりませんでした。</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($actions as $action)
                <div class="bg-white border-2 border-[#6BB6FF] rounded-xl p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-3 py-1 rounded-full body-small font-medium
                                    {{ $action['type'] === 'action_item' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $action['source'] }}
                                </span>
                                @if($action['milestone'])
                                    <span class="body-small text-[#1E3A5F]/60">
                                        マイルストーン: {{ $action['milestone'] }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="body-text font-semibold text-[#2E5C8A] mb-1">{{ $action['title'] }}</h3>
                            @if($action['description'])
                                <p class="body-small text-[#1E3A5F]/80">{{ $action['description'] }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="body-small text-[#1E3A5F]/60">
                                {{ $action['date']->format('Y年m月d日') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>




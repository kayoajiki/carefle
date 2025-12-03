<div class="card-refined p-8">
    <h2 class="heading-2 text-2xl text-[#2E5C8A] mb-6">内省アーカイブ</h2>

    {{-- フィルター --}}
    <div class="mb-6 space-y-4">
        {{-- 検索 --}}
        <div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="日記を検索..."
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- タイプフィルター --}}
            <div>
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">内省タイプ</label>
                <select
                    wire:model.live="filterType"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                >
                    <option value="all">すべて</option>
                    <option value="daily">今日の振り返り</option>
                    <option value="yesterday">昨日の振り返り</option>
                    <option value="weekly">週次振り返り</option>
                    <option value="deep">深い内省</option>
                    <option value="moya_moya">モヤモヤ解消</option>
                </select>
            </div>

            {{-- 日付From --}}
            <div>
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">開始日</label>
                <input
                    type="date"
                    wire:model.live="filterDateFrom"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                />
            </div>

            {{-- 日付To --}}
            <div>
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">終了日</label>
                <input
                    type="date"
                    wire:model.live="filterDateTo"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                />
            </div>
        </div>

        <div class="flex justify-end">
            <button
                wire:click="clearFilters"
                class="px-4 py-2 bg-[#E8F4FF] text-[#2E5C8A] body-small font-medium rounded-lg hover:bg-[#D0E8FF] transition-colors"
            >
                フィルターをクリア
            </button>
        </div>
    </div>

    {{-- 日記リスト --}}
    @if($diaries->isEmpty())
        <div class="text-center py-12">
            <p class="body-text text-[#1E3A5F]/60">該当する日記が見つかりませんでした。</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($diaries as $diary)
                <div class="bg-white border-2 border-[#6BB6FF] rounded-xl p-6 hover:shadow-lg transition-shadow cursor-pointer"
                     wire:click="selectDiary({{ $diary->id }})">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="body-text font-semibold text-[#2E5C8A]">
                                    {{ $diary->date->format('Y年m月d日') }}
                                </span>
                                @if($diary->reflection_type)
                                    <span class="px-3 py-1 rounded-full body-small font-medium bg-[#E8F4FF] text-[#2E5C8A]">
                                        @if($diary->reflection_type === 'daily')
                                            今日の振り返り
                                        @elseif($diary->reflection_type === 'yesterday')
                                            昨日の振り返り
                                        @elseif($diary->reflection_type === 'weekly')
                                            週次振り返り
                                        @elseif($diary->reflection_type === 'deep')
                                            深い内省
                                        @elseif($diary->reflection_type === 'moya_moya')
                                            モヤモヤ解消
                                        @endif
                                    </span>
                                @endif
                                @if($diary->motivation !== null)
                                    <span class="body-small text-[#1E3A5F]/60">
                                        モチベーション: {{ $diary->motivation }}/100
                                    </span>
                                @endif
                            </div>
                            <p class="body-text text-[#1E3A5F] line-clamp-3">
                                {{ $diary->content }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ページネーション --}}
        <div class="mt-6">
            {{ $diaries->links() }}
        </div>
    @endif
</div>


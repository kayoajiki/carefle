<div class="card-refined p-8">
    <h2 class="heading-2 text-2xl text-[#2E5C8A] mb-6">マイルストーン進捗</h2>

    @if($milestones->isEmpty())
        <div class="text-center py-12">
            <p class="body-text text-[#1E3A5F]/60">進行中のマイルストーンがありません。</p>
        </div>
    @else
        {{-- マイルストーン選択 --}}
        <div class="mb-6">
            <label class="body-small font-medium text-[#2E5C8A] mb-2 block">マイルストーンを選択</label>
            <select
                wire:model.live="selectedMilestoneId"
                wire:change="loadProgress($event.target.value)"
                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            >
                <option value="">選択してください</option>
                @foreach($milestones as $milestone)
                    <option value="{{ $milestone->id }}">{{ $milestone->title }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedMilestone && $progressData)
            <div class="space-y-6">
                {{-- 進捗サマリー --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- 完了率 --}}
                    <div class="bg-[#E8F4FF] rounded-xl p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="body-small text-[#1E3A5F]/60">完了率</span>
                            <span class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $progressData['completion_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-white rounded-full h-3 overflow-hidden">
                            <div 
                                class="h-3 bg-[#6BB6FF] transition-all duration-500"
                                style="width: {{ $progressData['completion_rate'] }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- 残り日数 --}}
                    @if($progressData['days_remaining'] !== null)
                        <div class="bg-[#E8F4FF] rounded-xl p-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="body-small text-[#1E3A5F]/60">残り日数</span>
                                <span class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $progressData['days_remaining'] }}日</span>
                            </div>
                            <p class="body-small text-[#1E3A5F]/60">
                                @if($progressData['days_remaining'] > 0)
                                    目標日まであと{{ $progressData['days_remaining'] }}日
                                @else
                                    目標日を過ぎています
                                @endif
                            </p>
                        </div>
                    @endif

                    {{-- 完了アクション数 --}}
                    <div class="bg-[#E8F4FF] rounded-xl p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="body-small text-[#1E3A5F]/60">完了アクション</span>
                            <span class="heading-3 text-2xl font-bold text-[#6BB6FF]">{{ $progressData['completed_actions'] }}/{{ $progressData['total_actions'] }}</span>
                        </div>
                        <p class="body-small text-[#1E3A5F]/60">
                            進行中: {{ $progressData['in_progress_actions'] }} | 
                            未着手: {{ $progressData['pending_actions'] }}
                        </p>
                    </div>
                </div>

                {{-- アクション詳細 --}}
                <div class="bg-white border-2 border-[#6BB6FF] rounded-xl p-6">
                    <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">アクションアイテム</h3>
                    <div class="space-y-3">
                        @forelse($selectedMilestone->actionItems as $actionItem)
                            <div class="flex items-center justify-between p-4 bg-[#F0F7FF] rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center
                                            {{ $actionItem->status === 'completed' ? 'bg-green-500' : ($actionItem->status === 'in_progress' ? 'bg-yellow-500' : 'bg-gray-300') }}
                                            text-white text-xs font-semibold">
                                            @if($actionItem->status === 'completed')
                                                ✓
                                            @elseif($actionItem->status === 'in_progress')
                                                →
                                            @else
                                                ○
                                            @endif
                                        </span>
                                        <h4 class="body-text font-medium text-[#2E5C8A]">{{ $actionItem->title }}</h4>
                                    </div>
                                    @if($actionItem->description)
                                        <p class="body-small text-[#1E3A5F]/80 ml-9">{{ $actionItem->description }}</p>
                                    @endif
                                </div>
                                <span class="body-small text-[#1E3A5F]/60 px-3 py-1 rounded-full
                                    {{ $actionItem->status === 'completed' ? 'bg-green-100 text-green-700' : ($actionItem->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                    @if($actionItem->status === 'completed')
                                        完了
                                    @elseif($actionItem->status === 'in_progress')
                                        進行中
                                    @else
                                        未着手
                                    @endif
                                </span>
                            </div>
                        @empty
                            <p class="body-text text-[#1E3A5F]/60 text-center py-4">アクションアイテムがありません。</p>
                        @endforelse
                    </div>
                </div>

                {{-- AIフィードバック --}}
                @if($feedback)
                    <div class="bg-[#E8F4FF] rounded-xl p-6">
                        <h3 class="heading-3 text-lg text-[#2E5C8A] mb-4">AIからのフィードバック</h3>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <p class="body-text text-[#1E3A5F] whitespace-pre-wrap flex-1">{{ $feedback }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @elseif($selectedMilestoneId)
            <div class="text-center py-12">
                <p class="body-text text-[#1E3A5F]/60">進捗データを読み込み中...</p>
            </div>
        @else
            <div class="text-center py-12">
                <p class="body-text text-[#1E3A5F]/60">マイルストーンを選択してください。</p>
            </div>
        @endif
    @endif
</div>



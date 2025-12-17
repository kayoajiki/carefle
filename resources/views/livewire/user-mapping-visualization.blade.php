<div>
@if(!$isUnlocked)
    {{-- アンロックされていない場合 --}}
    <div class="card-refined surface-blue p-8 text-center">
        <p class="body-text text-[#1E3A5F]/70">
            オンボーディングを完了すると、曼荼羅マッピングが表示されます。
        </p>
    </div>
@elseif($mapping)
    {{-- 曼荼羅形式で表示 --}}
    <div class="space-y-8">
        {{-- 曼荼羅の説明 --}}
        <div class="card-refined surface-blue p-6">
            <h3 class="heading-3 text-[#2E5C8A] mb-4">曼荼羅マッピング</h3>
            <p class="body-text text-[#1E3A5F]/70 mb-2">
                外側=未来、中央=現在、内側=過去の構造で、あなたの変容を可視化します。
            </p>
            <p class="body-small text-[#1E3A5F]/60">
                完了した項目のみ表示されます。
            </p>
        </div>

        {{-- 未来（外側） --}}
        @if(!empty($mapping['future']['items']))
        <div class="card-refined surface-blue p-6 border-2 border-[#6BB6FF]">
            <h4 class="heading-4 text-xl text-[#2E5C8A] mb-4">未来の自分</h4>
            <div class="space-y-4">
                @if(isset($mapping['future']['items']['wcm_sheet']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">WCMシート</h5>
                    @foreach($mapping['future']['items']['wcm_sheet']['data'] as $sheet)
                    <div class="mb-3 last:mb-0">
                        <p class="body-text text-[#1E3A5F] font-medium">{{ $sheet['title'] }}</p>
                        @if($sheet['will_text'])
                        <p class="body-small text-[#1E3A5F]/70 mt-1">
                            <span class="font-semibold">Will:</span> {{ mb_substr($sheet['will_text'], 0, 100) }}...
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if(isset($mapping['future']['items']['milestones']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">マイルストーン</h5>
                    @foreach($mapping['future']['items']['milestones']['data'] as $milestone)
                    <div class="mb-3 last:mb-0">
                        <p class="body-text text-[#1E3A5F] font-medium">{{ $milestone['title'] }}</p>
                        @if($milestone['target_year'])
                        <p class="body-small text-[#1E3A5F]/70 mt-1">
                            目標年: {{ $milestone['target_year'] }}年
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- 現在（中央） --}}
        @if(!empty($mapping['current']['items']))
        <div class="card-refined surface-blue p-6 border-2 border-[#6BB6FF]/70">
            <h4 class="heading-4 text-xl text-[#2E5C8A] mb-4">現在の自分</h4>
            <div class="space-y-4">
                @if(isset($mapping['current']['items']['current_diagnosis']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">最新の診断</h5>
                    <div class="flex gap-4">
                        <p class="body-text text-[#1E3A5F]">
                            仕事の満足度: <span class="font-semibold">{{ $mapping['current']['items']['current_diagnosis']['data']['work_score'] }}点</span>
                        </p>
                        <p class="body-text text-[#1E3A5F]">
                            生活の満足度: <span class="font-semibold">{{ $mapping['current']['items']['current_diagnosis']['data']['life_score'] }}点</span>
                        </p>
                    </div>
                </div>
                @endif

                @if(isset($mapping['current']['items']['current_diaries']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">最近の日記</h5>
                    <p class="body-small text-[#1E3A5F]/70">
                        {{ count($mapping['current']['items']['current_diaries']['data']) }}件の日記を記録
                    </p>
                </div>
                @endif

                @if(isset($mapping['current']['items']['strengths_report']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">持ち味レポ</h5>
                    <p class="body-small text-[#1E3A5F]/70">
                        生成済み
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- 過去（内側） --}}
        @if(!empty($mapping['past']['items']))
        <div class="card-refined surface-blue p-6 border-2 border-[#6BB6FF]/40">
            <h4 class="heading-4 text-xl text-[#2E5C8A] mb-4">過去の自分</h4>
            <div class="space-y-4">
                @if(isset($mapping['past']['items']['past_diagnosis']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">過去の診断</h5>
                    <p class="body-small text-[#1E3A5F]/70">
                        {{ count($mapping['past']['items']['past_diagnosis']['data']) }}件の診断記録
                    </p>
                </div>
                @endif

                @if(isset($mapping['past']['items']['past_diaries']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">過去の日記</h5>
                    <p class="body-small text-[#1E3A5F]/70">
                        {{ count($mapping['past']['items']['past_diaries']['data']) }}件の日記記録
                    </p>
                </div>
                @endif

                @if(isset($mapping['past']['items']['life_history']))
                <div class="bg-white/50 rounded-lg p-4">
                    <h5 class="font-semibold text-[#2E5C8A] mb-2">人生史</h5>
                    <p class="body-small text-[#1E3A5F]/70">
                        {{ count($mapping['past']['items']['life_history']['data']) }}件の人生イベント
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- データがない場合 --}}
        @if(empty($mapping['future']['items']) && empty($mapping['current']['items']) && empty($mapping['past']['items']))
        <div class="card-refined surface-blue p-8 text-center">
            <p class="body-text text-[#1E3A5F]/70">
                マッピング項目を完了すると、ここに表示されます。
            </p>
        </div>
        @endif
    </div>
@endif
</div>

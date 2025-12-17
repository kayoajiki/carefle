<div>
@if(!$isUnlocked)
    {{-- アンロックされていない場合 --}}
    <div class="card-refined surface-blue p-8 text-center">
        <p class="body-text text-[#1E3A5F]/70">
            オンボーディングを完了すると、変容の可視化が表示されます。
        </p>
    </div>
@else
    {{-- 変容の可視化 --}}
    <div class="space-y-8">
        {{-- 変容ポイント --}}
        @if($transformationCount > 0)
        <div class="card-refined surface-blue p-6">
            <h3 class="heading-3 text-[#2E5C8A] mb-4">変容ポイント</h3>
            <p class="body-text text-[#1E3A5F]/70 mb-6">
                過去と現在を比較して、{{ $transformationCount }}つの変容ポイントが見つかりました。
            </p>
            
            <div class="space-y-4">
                @foreach($transformations as $transformation)
                <div class="bg-white/50 rounded-lg p-4 border border-[#6BB6FF]/20">
                    <h4 class="heading-4 text-lg text-[#2E5C8A] mb-2">{{ $transformation['title'] }}</h4>
                    <p class="body-text text-[#1E3A5F]/70 mb-4">{{ $transformation['description'] }}</p>
                    
                    @if($transformation['type'] === 'diagnosis')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#E8F4FF] rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">過去</p>
                            <p class="body-text text-[#1E3A5F]">
                                仕事: {{ $transformation['past_value']['work_score'] }}点 / 
                                生活: {{ $transformation['past_value']['life_score'] }}点
                            </p>
                        </div>
                        <div class="bg-[#6BB6FF]/10 rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">現在</p>
                            <p class="body-text text-[#1E3A5F]">
                                仕事: {{ $transformation['current_value']['work_score'] }}点 / 
                                生活: {{ $transformation['current_value']['life_score'] }}点
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        @if($transformation['change']['work_score'] > 0)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                仕事 +{{ $transformation['change']['work_score'] }}点
                            </span>
                        @elseif($transformation['change']['work_score'] < 0)
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                仕事 {{ $transformation['change']['work_score'] }}点
                            </span>
                        @endif
                        @if($transformation['change']['life_score'] > 0)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                生活 +{{ $transformation['change']['life_score'] }}点
                            </span>
                        @elseif($transformation['change']['life_score'] < 0)
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                生活 {{ $transformation['change']['life_score'] }}点
                            </span>
                        @endif
                    </div>
                    @elseif($transformation['type'] === 'motivation')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#E8F4FF] rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">過去の平均</p>
                            <p class="body-text text-[#1E3A5F]">{{ $transformation['past_value'] }}点</p>
                        </div>
                        <div class="bg-[#6BB6FF]/10 rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">現在の平均</p>
                            <p class="body-text text-[#1E3A5F]">{{ $transformation['current_value'] }}点</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if($transformation['change'] > 0)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                +{{ $transformation['change'] }}点
                            </span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                {{ $transformation['change'] }}点
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card-refined surface-blue p-8 text-center">
            <p class="body-text text-[#1E3A5F]/70">
                過去と現在のデータを比較して、変容ポイントを抽出します。データが蓄積されると表示されます。
            </p>
        </div>
        @endif

        {{-- 成長グラフ --}}
        @if(!empty($growthData))
        <div class="card-refined surface-blue p-6">
            <h3 class="heading-3 text-[#2E5C8A] mb-4">成長の軌跡（過去6ヶ月）</h3>
            <p class="body-text text-[#1E3A5F]/70 mb-6">
                月別の満足度とモチベーションの推移を可視化します。
            </p>
            
            <div class="space-y-4">
                @foreach($growthData as $month)
                <div class="bg-white/50 rounded-lg p-4 border border-[#6BB6FF]/20">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="heading-4 text-lg text-[#2E5C8A]">{{ $month['month'] }}</h4>
                        <span class="body-small text-[#1E3A5F]/70">日記: {{ $month['diary_count'] }}件</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($month['work_score'] !== null)
                        <div class="bg-[#E8F4FF] rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">仕事の満足度</p>
                            <p class="body-text text-[#1E3A5F] font-semibold">{{ $month['work_score'] }}点</p>
                        </div>
                        @endif
                        
                        @if($month['life_score'] !== null)
                        <div class="bg-[#E8F4FF] rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">生活の満足度</p>
                            <p class="body-text text-[#1E3A5F] font-semibold">{{ $month['life_score'] }}点</p>
                        </div>
                        @endif
                        
                        @if($month['avg_motivation'] !== null)
                        <div class="bg-[#E8F4FF] rounded-lg p-3">
                            <p class="body-small text-[#1E3A5F]/70 mb-1">平均モチベーション</p>
                            <p class="body-text text-[#1E3A5F] font-semibold">{{ $month['avg_motivation'] }}点</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
@endif
</div>

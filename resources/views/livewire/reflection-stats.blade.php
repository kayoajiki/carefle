<div class="card-refined p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="heading-3 text-xl">内省統計</h2>
        <div class="flex gap-2">
            <button
                wire:click="$set('selectedPeriod', 'week')"
                class="px-4 py-2 rounded-lg body-small font-medium transition-colors {{ $selectedPeriod === 'week' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                週次
            </button>
            <button
                wire:click="$set('selectedPeriod', 'month')"
                class="px-4 py-2 rounded-lg body-small font-medium transition-colors {{ $selectedPeriod === 'month' ? 'bg-[#6BB6FF] text-white' : 'bg-[#E8F4FF] text-[#2E5C8A] hover:bg-[#D0E8FF]' }}"
            >
                月次
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#E8F4FF] rounded-xl p-6">
            <p class="body-small text-[#1E3A5F]/60 mb-2">連続記録日数</p>
            <p class="heading-2 text-[#6BB6FF] flex items-center gap-2">
                <span>🔥</span>
                <span>{{ $streak }}日</span>
            </p>
        </div>
        <div class="bg-[#E8F4FF] rounded-xl p-6">
            <p class="body-small text-[#1E3A5F]/60 mb-2">今週の内省</p>
            <p class="heading-2 text-[#2E5C8A]">{{ $weeklyCount }}回</p>
        </div>
        <div class="bg-[#E8F4FF] rounded-xl p-6">
            <p class="body-small text-[#1E3A5F]/60 mb-2">今月の内省</p>
            <p class="heading-2 text-[#2E5C8A]">{{ $monthlyCount }}回</p>
        </div>
    </div>

    @if(count($motivationTrend) > 0)
        <div class="mb-8">
            <h3 class="heading-3 text-lg mb-4">モチベーション推移</h3>
            <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                <canvas id="motivationChart" width="400" height="200"></canvas>
            </div>
        </div>
    @endif

    @if(array_sum($typeDistribution) > 0)
        <div>
            <h3 class="heading-3 text-lg mb-4">内省タイプの分布</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @php
                    $typeLabels = [
                        'daily' => '1日の振り返り',
                        'yesterday' => '昨日の振り返り',
                        'weekly' => '週次振り返り',
                        'deep' => '深い内省',
                        'moya_moya' => 'モヤモヤ解消',
                    ];
                @endphp
                @foreach($typeDistribution as $type => $count)
                    @if($count > 0)
                        <div class="bg-[#E8F4FF] rounded-xl p-4 text-center">
                            <p class="body-small text-[#1E3A5F]/60 mb-1">{{ $typeLabels[$type] ?? $type }}</p>
                            <p class="heading-3 text-[#2E5C8A]">{{ $count }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
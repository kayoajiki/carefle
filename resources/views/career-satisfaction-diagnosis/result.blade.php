<x-layouts.app.sidebar :title="'職業満足度診断結果'">
    <flux:main>
<div class="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
    @php
        // 状態タイプ別の色とメッセージ
        $stateTypeConfig = [
            'A' => [
                'color' => 'blue',
                'bgColor' => 'bg-blue-50',
                'borderColor' => 'border-blue-300',
                'textColor' => 'text-blue-800',
                'title' => '一人で内省を続けられる状態',
                'description' => '理想とギャップのある領域はありますが、混乱していません。言語化できたこと自体に納得感があり、方向性を急いでいない状態です。',
                'actionTitle' => 'CareFreの継続利用を推奨',
                'actionDescription' => '状態の変化を追跡し、思考の履歴を残し続けることで、急いで判断しない状態を維持できます。CareFreは判断を下すためのツールではなく、判断を急がない状態を保つための場所です。',
            ],
            'B' => [
                'color' => 'orange',
                'bgColor' => 'bg-orange-50',
                'borderColor' => 'border-orange-300',
                'textColor' => 'text-orange-800',
                'title' => '一人だと堂々巡りになりやすい状態',
                'description' => '理想とギャップのある領域が集中しているか、複数の領域に深刻なギャップがあります。言語化はできたものの、解釈が揺れ、考えれば考えるほど不安が増える感覚があります。',
                'actionTitle' => '誰かと話して整理することを検討',
                'actionDescription' => '何が邪魔をしているかを明確にするため、一人で考え続けるのではなく、誰かと話して頭の中を整理することを選択肢として検討できます。今すぐ決める必要はありません。',
            ],
            'C' => [
                'color' => 'green',
                'bgColor' => 'bg-green-50',
                'borderColor' => 'border-green-300',
                'textColor' => 'text-green-800',
                'title' => '今は動かない判断が妥当な状態',
                'description' => '安定ゾーンが広く、理想とギャップのある領域が軽微またはありません。大きなアクションは不要で、予防的な利用や定期的な状態確認が適切です。',
                'actionTitle' => 'CareFreの継続利用を推奨',
                'actionDescription' => '定期的な状態チェック（通知、再診断）を通じて、大きな問題が発生する前に早期に気づくことができます。',
            ],
        ];
        
        $config = $stateType ? ($stateTypeConfig[$stateType] ?? $stateTypeConfig['A']) : $stateTypeConfig['A'];
        
        // 関係性の距離感を言語化（簡易版）
        $relationshipText = '';
        if ($stuckPointCount === 0) {
            $relationshipText = '現在の仕事との関係は、全体的にバランスが取れている状態です。';
        } elseif ($stuckPointCount <= 2 && $maxDiff >= -10) {
            $relationshipText = '現在の仕事との関係は、一部に軽微な理想とのギャップがあるものの、全体的には安定しています。';
        } elseif ($stuckPointCount <= 2 && $maxDiff < -10) {
            $relationshipText = '現在の仕事との関係は、特定の領域で理想とのギャップを感じている状態です。';
        } else {
            $relationshipText = '現在の仕事との関係は、複数の領域で理想とのギャップを感じている状態です。';
        }
    @endphp

    <div class="w-full max-w-6xl mx-auto space-y-10">
        <!-- 第一ビュー：関係性の距離感 -->
        <div class="card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff]">
            <div class="mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <p class="body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2">
                            あなたと仕事の関係
                        </p>
                        <h1 class="heading-2 text-3xl md:text-4xl mb-4">
                            職業満足度診断結果
                        </h1>
                    </div>
                    <div class="ml-4 flex items-center gap-3">
                        @if($diagnosis->is_admin_visible)
                            <span class="text-sm px-3 py-2 rounded-xl bg-green-50 border border-green-300 text-green-700 font-medium">
                                管理者に共有中
                            </span>
                            <form action="{{ route('share-preview.unshare') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="type" value="career_satisfaction">
                                <input type="hidden" name="id" value="{{ $diagnosis->id }}">
                                <button type="submit" onclick="return confirm('共有を解除しますか？')" class="btn-secondary text-sm">
                                    共有を解除
                                </button>
                            </form>
                        @else
                            <a href="{{ route('share-preview.career-satisfaction', ['id' => $diagnosis->id]) }}" class="btn-secondary text-sm">
                                管理者に共有する
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="bg-white/60 rounded-xl p-6 border-2 border-[#6BB6FF]/30">
                <p class="body-large text-[#1E3A5F] leading-relaxed">
                    {{ $relationshipText }}
                </p>
            </div>
        </div>

        <!-- 状態サマリー -->
        <div class="card-refined p-8">
            <h2 class="heading-3 text-2xl mb-4 text-[#1E3A5F]">
                状態サマリー
            </h2>
            <p class="body-text text-[#4A5A73] mb-4">
                これは「問題」ではなく、「判断前の状態」としての進捗レポートです。
            </p>
            <div class="bg-[#F0F7FF] rounded-xl p-6 border border-[#6BB6FF]/20">
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">📊</span>
                        <div>
                            <p class="body-text font-semibold text-[#1E3A5F] mb-1">満足度スコア</p>
                            <p class="heading-1 text-4xl text-[#2E5C8A]">{{ $workScore }}<span class="text-xl">/100</span></p>
                        </div>
                    </div>
                    @if($stuckPointCount > 0)
                        <div class="flex items-start gap-3 pt-3 border-t border-[#6BB6FF]/20">
                            <span class="text-2xl">📍</span>
                            <div>
                                <p class="body-text font-semibold text-[#1E3A5F] mb-1">理想とギャップのある領域</p>
                                <p class="body-large text-[#2E5C8A]">{{ $stuckPointCount }}個の領域で理想とギャップがあります</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 図表（ポジション可視化） -->
        <div class="card-refined p-8">
            <h2 class="heading-3 text-2xl mb-4 text-[#1E3A5F]">
                あなたの位置
            </h2>
            <p class="body-text text-[#4A5A73] mb-6">
                考え始めている位置を可視化しました。「ポジティブ/ロスト」ではなく、「今どこにいるか」を示しています。
            </p>
            <div class="bg-white rounded-xl p-6 border-2 border-[#6BB6FF]/20">
                <!-- レーダーチャート -->
                <canvas id="radarChart" width="400" height="400"></canvas>
            </div>
        </div>

        <!-- 理想とギャップのある領域 -->
        @if($stuckPointCount > 0)
            <div class="card-refined p-8">
                <h2 class="heading-3 text-2xl mb-4 text-[#1E3A5F]">
                    理想とギャップのある領域
                </h2>
                <p class="body-text text-[#4A5A73] mb-6">
                    重要度（理想）と満足度にギャップがある領域です。深掘りはせず、現状を把握するための情報としてご覧ください。
                </p>
                <div class="space-y-4">
                    @foreach($stuckPointDetails as $pillar => $detail)
                        <div class="bg-[#FFF4E6] rounded-xl p-6 border-2 border-orange-200">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="body-text font-semibold text-[#1E3A5F]">{{ $detail['label'] }}</h3>
                                <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800 body-small font-semibold">
                                    差分: {{ $detail['diff'] }}点
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p class="body-small text-[#4A5A73] mb-1">満足度</p>
                                    <p class="heading-2 text-2xl text-[#2E5C8A]">{{ $detail['satisfaction'] }}<span class="text-sm">/100</span></p>
                                </div>
                                <div>
                                    <p class="body-small text-[#4A5A73] mb-1">重要度</p>
                                    <p class="heading-2 text-2xl text-[#2E5C8A]">{{ $detail['importance'] }}<span class="text-sm">/100</span></p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 今の状態について -->
        <div class="card-refined p-8 bg-gradient-to-br from-green-50 to-blue-50 border-2 border-green-200">
            <h2 class="heading-3 text-2xl mb-4 text-[#1E3A5F]">
                今の状態について
            </h2>
            
            @if($stuckPointCount > 0)
                <div class="bg-white/60 rounded-xl p-6 border border-green-200 mb-4">
                    <p class="body-text text-[#1E3A5F] mb-3 font-semibold">
                        5つの領域のうち、{{ $stuckPointCount }}個の領域で理想とのギャップがあります。
                    </p>
                    <div class="space-y-2">
                        @if(count($gapSummary['severe']) > 0)
                            <p class="body-text text-[#4A5A73]">
                                <span class="font-semibold text-orange-700">特に大きいギャップ：</span>
                                {{ implode('、', $gapSummary['severe']) }}
                            </p>
                        @endif
                        @if(count($gapSummary['moderate']) > 0)
                            <p class="body-text text-[#4A5A73]">
                                <span class="font-semibold text-orange-600">中程度のギャップ：</span>
                                {{ implode('、', $gapSummary['moderate']) }}
                            </p>
                        @endif
                        @if(count($gapSummary['mild']) > 0)
                            <p class="body-text text-[#4A5A73]">
                                <span class="font-semibold text-blue-600">軽微なギャップ：</span>
                                {{ implode('、', $gapSummary['mild']) }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white/60 rounded-xl p-6 border border-green-200 mb-4">
                    <p class="body-text text-[#1E3A5F]">
                        5つの領域すべてで、理想と満足度のバランスが取れています。
                    </p>
                </div>
            @endif
            
            <p class="body-text text-[#4A5A73] leading-relaxed">
                理想とギャップのある領域があっても、それは「問題」ではなく「現状の把握」です。この状態を維持しながら、無理をせず、次のステップを一緒に考えていきましょう。
            </p>
        </div>

        <!-- 状態判定結果（最重要） -->
        @if($stateType)
            <div class="card-refined p-8 {{ $config['bgColor'] }} border-2 {{ $config['borderColor'] }}">
                <div class="mb-6">
                    <p class="body-small uppercase tracking-[0.2em] {{ $config['textColor'] }} mb-2">
                        状態タイプ: {{ $stateType }}
                    </p>
                    <h2 class="heading-2 text-3xl {{ $config['textColor'] }} mb-3">
                        {{ $config['title'] }}
                    </h2>
                    <p class="body-large {{ $config['textColor'] }} leading-relaxed">
                        {{ $config['description'] }}
                    </p>
                </div>
                
                <div class="bg-white/60 rounded-xl p-6 @if($stateType === 'A') border-blue-200 @elseif($stateType === 'B') border-orange-200 @else border-green-200 @endif border">
                    <h3 class="body-text font-semibold {{ $config['textColor'] }} mb-3">
                        {{ $config['actionTitle'] }}
                    </h3>
                    <p class="body-text {{ $config['textColor'] }} leading-relaxed">
                        {{ $config['actionDescription'] }}
                    </p>
                    
                    @if($stateType === 'B')
                        <div class="mt-6 pt-6 border-t border-orange-200">
                            <p class="body-text text-orange-800 mb-4">
                                誰かと話して整理する方法として、キャリハグ（１対１の面談）を選択肢として検討できます。
                            </p>
                            <p class="body-small text-orange-800 opacity-80">
                                ※今すぐ決める必要はありません。まずは自分の状態を理解することが大切です。
                            </p>
                        </div>
                    @elseif($stateType === 'C')
                        <div class="mt-6 pt-6 border-t border-green-200">
                            <p class="body-text text-green-800">
                                CareFreの通知機能や再診断機能を活用して、定期的に状態を確認することをおすすめします。
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- ナビゲーション -->
        <div class="flex justify-center gap-4 pt-6">
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                キャリフレを続ける
            </a>
            <a href="{{ route('career-satisfaction-diagnosis.start') }}" class="btn-secondary">
                もう一度診断する
            </a>
            @if($stateType === 'B')
                <a href="#" class="btn-primary">
                    キャリハグについて詳しく
                </a>
            @endif
        </div>
    </div>

    <!-- Chart.js for radar chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('radarChart');
            if (!ctx) return;

            const labels = @json($radarLabels);
            const workData = @json($radarWorkData);
            const importanceData = @json($importanceDataset);

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '満足度',
                            data: workData,
                            borderColor: 'rgb(107, 182, 255)',
                            backgroundColor: 'rgba(107, 182, 255, 0.2)',
                            pointBackgroundColor: 'rgb(107, 182, 255)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(107, 182, 255)',
                        },
                        {
                            label: '重要度',
                            data: importanceData,
                            borderColor: 'rgb(139, 190, 220)',
                            backgroundColor: 'rgba(139, 190, 220, 0.2)',
                            pointBackgroundColor: 'rgb(139, 190, 220)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(139, 190, 220)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</div>
    </flux:main>
</x-layouts.app.sidebar>


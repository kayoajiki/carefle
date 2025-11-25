<div class="content-padding section-spacing-sm">
    <div class="w-full max-w-6xl mx-auto space-y-10">
        <!-- ヘッダー -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="heading-2 mb-2">自己診断の可視化</h1>
                <p class="body-large text-[#1E3A5F]">
                    登録済みの診断結果を一望し、傾向や変化を振り返りましょう。
                </p>
            </div>
            <a href="{{ route('assessments.index') }}" class="btn-secondary text-sm text-center w-full md:w-auto">
                診断結果を編集する
            </a>
        </div>

        <!-- MBTI -->
        <div class="card-refined p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="heading-3 text-xl mb-1">MBTI</h2>
                    <p class="body-small text-[#1E3A5F]">最新のタイプと各指標を確認</p>
                </div>
            </div>

            @if($mbtiLatest)
                @php
                    $mbti = $mbtiLatest->formatted_result;
                @endphp
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="body-small text-[#1E3A5F] mb-2">最新タイプ</p>
                        <p class="heading-2 text-4xl text-[#2E5C8A]">{{ $mbti['type'] ?? 'N/A' }}</p>
                        @if($mbtiLatest->completed_at)
                            <p class="body-small text-[#1E3A5F] mt-2">
                                {{ $mbtiLatest->completed_at->format('Y年n月j日') }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <p class="body-small text-[#1E3A5F] mb-4">各指標のバランス</p>
                        <div class="space-y-3">
                            @foreach(($mbti['percentage'] ?? []) as $axis => $value)
                                <div>
                                    <div class="flex justify-between body-small text-[#1E3A5F] mb-1">
                                        <span>{{ $axis }}</span>
                                        <span>{{ $value }}%</span>
                                    </div>
                                    <div class="w-full h-2 bg-[#E8F4FF] rounded-full overflow-hidden">
                                        <div class="h-full bg-[#6BB6FF]" style="width: {{ $value }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <p class="body-text text-[#1E3A5F]">MBTI診断の結果がまだ登録されていません。</p>
            @endif
        </div>

        <!-- Strengths Finder -->
        <div class="card-refined p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="heading-3 text-xl mb-1">ストレングスファインダー</h2>
                    <p class="body-small text-[#1E3A5F]">上位5つの強みを視覚化</p>
                </div>
            </div>

            @if($strengthLatest)
                @php
                    $strength = $strengthLatest->formatted_result;
                    $top5 = $strength['top5'] ?? [];
                @endphp
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-3">
                        <p class="body-small text-[#1E3A5F]">上位5つの強み</p>
                        <ol class="space-y-2 list-decimal pl-6 text-[#2E5C8A] body-text">
                            @forelse($top5 as $index => $item)
                                <li><span class="font-semibold">{{ $item }}</span></li>
                            @empty
                                <li>未登録</li>
                            @endforelse
                        </ol>
                    </div>
                    <div>
                        <p class="body-small text-[#1E3A5F] mb-2">記録日</p>
                        @if($strengthLatest->completed_at)
                            <p class="body-text text-[#2E5C8A]">{{ $strengthLatest->completed_at->format('Y年n月j日') }}</p>
                        @else
                            <p class="body-text text-[#1E3A5F]">記録日未入力</p>
                        @endif
                        @if(!empty($strengthLatest->notes))
                            <p class="body-small text-[#1E3A5F] mt-4">{{ $strengthLatest->notes }}</p>
                        @endif
                    </div>
                </div>
            @else
                <p class="body-text text-[#1E3A5F]">ストレングスファインダーの結果がまだ登録されていません。</p>
            @endif
        </div>

        <!-- Enneagram & Big5 -->
        <div class="grid gap-8 md:grid-cols-2">
            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-4">エニアグラム</h2>
                @if($enneagramLatest)
                    @php
                        $enneagram = $enneagramLatest->formatted_result;
                    @endphp
                    <div class="space-y-3">
                        <div>
                            <p class="body-small text-[#1E3A5F] mb-1">タイプ</p>
                            <p class="heading-3 text-2xl text-[#2E5C8A]">
                                タイプ{{ $enneagram['type'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 body-small text-[#1E3A5F]">
                            <div>
                                <p class="font-semibold text-[#2E5C8A] mb-1">ウィング</p>
                                <p>{{ $enneagram['wing'] ?? '未入力' }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-[#2E5C8A] mb-1">トリタイプ</p>
                                <p>{{ $enneagram['tritype'] ?? '未入力' }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-[#2E5C8A] mb-1">本能型</p>
                                <p>{{ $enneagram['instinctual_variant'] ?? '未入力' }}</p>
                            </div>
                            @if($enneagramLatest->completed_at)
                                <div>
                                    <p class="font-semibold text-[#2E5C8A] mb-1">記録日</p>
                                    <p>{{ $enneagramLatest->completed_at->format('Y年n月j日') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="body-text text-[#1E3A5F]">エニアグラム結果がまだ登録されていません。</p>
                @endif
            </div>

            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-4">ビッグファイブ</h2>
                @if($big5Latest)
                    @php
                        $big5 = $big5Latest->formatted_result;
                        $labels = [
                            'openness' => '開放性 (Openness)',
                            'conscientiousness' => '誠実性 (Conscientiousness)',
                            'extraversion' => '外向性 (Extraversion)',
                            'agreeableness' => '協調性 (Agreeableness)',
                            'neuroticism' => '神経症傾向 (Neuroticism)',
                        ];
                    @endphp
                    <div class="space-y-3">
                        @foreach($labels as $key => $label)
                            <div>
                                <div class="flex justify-between body-small text-[#1E3A5F] mb-1">
                                    <span>{{ $label }}</span>
                                    <span>{{ $big5[$key] ?? 0 }}%</span>
                                </div>
                                <div class="w-full h-2 bg-[#E8F4FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#4A90E2]" style="width: {{ $big5[$key] ?? 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($big5Latest->completed_at)
                        <p class="body-small text-[#1E3A5F] mt-4">
                            診断日: {{ $big5Latest->completed_at->format('Y年n月j日') }}
                        </p>
                    @endif
                @else
                    <p class="body-text text-[#1E3A5F]">ビッグファイブの結果がまだ登録されていません。</p>
                @endif
            </div>
        </div>

        <!-- 履歴 -->
        <div class="card-refined p-8">
            <h2 class="heading-3 text-xl mb-4">診断履歴</h2>
            @php
                $history = collect([
                    $mbtiAssessments ?? collect(),
                    $strengthAssessments ?? collect(),
                    $enneagramAssessments ?? collect(),
                    $big5Assessments ?? collect(),
                ])->flatten();
            @endphp
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#2E5C8A]/10">
                    <thead>
                        <tr class="text-left body-small text-[#1E3A5F]">
                            <th class="py-3">診断名</th>
                            <th class="py-3">タイプ/結果</th>
                            <th class="py-3">記録日</th>
                            <th class="py-3">メモ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2E5C8A]/10 body-text text-[#1E3A5F]">
                        @forelse($history as $assessment)
                                @php
                                    $result = $assessment->formatted_result;
                                @endphp
                                <tr>
                                    <td class="py-3">{{ $assessment->assessment_name ?? strtoupper($assessment->assessment_type) }}</td>
                                    <td class="py-3">
                                        @switch($assessment->assessment_type)
                                            @case('mbti')
                                                {{ $result['type'] ?? 'N/A' }}
                                                @break
                                            @case('strengthsfinder')
                                                @if(!empty($result['top5']))
                                                    上位5: {{ implode(' / ', $result['top5']) }}
                                                @else
                                                    未入力
                                                @endif
                                                @break
                                            @case('enneagram')
                                                タイプ{{ $result['type'] ?? 'N/A' }}
                                                @break
                                            @case('big5')
                                                平均 {{ collect($result)->avg() ? number_format(collect($result)->avg(), 1) : 'N/A' }}%
                                                @break
                                            @default
                                                -
                                        @endswitch
                                    </td>
                                    <td class="py-3">
                                        @if($assessment->completed_at)
                                            {{ $assessment->completed_at->format('Y/m/d') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        {{ \Illuminate\Support\Str::limit($assessment->notes, 40) }}
                                    </td>
                                </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-[#1E3A5F]">診断履歴がありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

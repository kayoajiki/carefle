<x-admin.layouts.app title="自己診断結果: {{ $user->name }}">
    <div class="min-h-screen w-full bg-[#F0F7FF] text-[#1E3A5F] content-padding section-spacing-sm">
        {{-- ヘッダー --}}
        <div class="max-w-4xl mx-auto mb-12">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
                <div>
                    <h1 class="heading-2 mb-4">
                        {{ $user->name }}さんの自己診断結果
                    </h1>
                    <p class="body-large">
                        ユーザーが登録した自己診断結果を確認できます。
                    </p>
                </div>
                <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" class="btn-secondary text-sm">
                    ユーザー詳細に戻る
                </a>
            </div>
        </div>

        {{-- 自己診断結果表示 --}}
        <div class="max-w-4xl mx-auto">
            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-6">
                    {{ $assessment->assessment_name ?? strtoupper($assessment->assessment_type) }}
                </h2>
                
                <div class="space-y-6">
                    @php
                        $formattedResult = $assessment->formatted_result;
                    @endphp

                    {{-- MBTI --}}
                    @if($assessment->assessment_type === 'mbti' && isset($formattedResult['type']))
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">タイプ</h3>
                        <p class="heading-1 text-4xl text-[#2E5C8A] mb-4">{{ $formattedResult['type'] }}</p>
                        @if(isset($formattedResult['percentage']))
                        <div class="space-y-3">
                            @foreach($formattedResult['percentage'] as $axis => $value)
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
                        @endif
                    </div>
                    @endif

                    {{-- ストレングスファインダー --}}
                    @if($assessment->assessment_type === 'strengthsfinder' && isset($formattedResult['top5']))
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">トップ5の強み</h3>
                        <ul class="space-y-2">
                            @foreach($formattedResult['top5'] as $index => $strength)
                            <li class="body-text text-[#1E3A5F]">
                                <span class="font-semibold text-[#2E5C8A]">{{ $index + 1 }}.</span> {{ $strength }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- エニアグラム --}}
                    @if($assessment->assessment_type === 'enneagram' && isset($formattedResult['type']))
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">タイプ</h3>
                        <p class="heading-1 text-4xl text-[#2E5C8A] mb-2">{{ $formattedResult['type'] }}</p>
                        @if(isset($formattedResult['wing']))
                        <p class="body-text text-[#1E3A5F]">ウィング: {{ $formattedResult['wing'] }}</p>
                        @endif
                    </div>
                    @endif

                    {{-- ビッグファイブ --}}
                    @if($assessment->assessment_type === 'big5' && isset($formattedResult))
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">5因子スコア</h3>
                        <div class="space-y-3">
                            @foreach($formattedResult as $factor => $value)
                            @if(is_numeric($value))
                            <div>
                                <div class="flex justify-between body-small text-[#1E3A5F] mb-1">
                                    <span>{{ $factor }}</span>
                                    <span>{{ $value }}%</span>
                                </div>
                                <div class="w-full h-2 bg-[#E8F4FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#6BB6FF]" style="width: {{ $value }}%"></div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- FFS理論 --}}
                    @if($assessment->assessment_type === 'ffs' && isset($formattedResult))
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">5つの特性</h3>
                        <div class="space-y-3">
                            @foreach($formattedResult as $trait => $value)
                            @if(is_numeric($value))
                            <div>
                                <div class="flex justify-between body-small text-[#1E3A5F] mb-1">
                                    <span>{{ $trait }}</span>
                                    <span>{{ $value }}%</span>
                                </div>
                                <div class="w-full h-2 bg-[#E8F4FF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#6BB6FF]" style="width: {{ $value }}%"></div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- メモ --}}
                    @if($assessment->notes)
                    <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                        <h3 class="heading-3 text-lg mb-4">メモ</h3>
                        <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $assessment->notes }}</p>
                    </div>
                    @endif

                    {{-- 記録日 --}}
                    <div class="text-sm text-[#1E3A5F]/70">
                        <p>記録日: {{ $assessment->completed_at ? $assessment->completed_at->format('Y年n月j日') : $assessment->created_at->format('Y年n月j日') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
